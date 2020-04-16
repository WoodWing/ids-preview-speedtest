<?php declare( strict_types=1 );

class WW_WwTest_IdsSpeedTest_ServiceProxy
{
	/** @var string */
	private $ticket;

	/** @var string */
	private $workspaceId;

	/** @var int */
	private $layoutId;

	/** @var int */
	private $articleId;

	/** @var WflObject */
	private $article;

	/** @var bool */
	private $tookArticleLock = false;

	public function logOn(): void
	{
		$suiteOpts = unserialize( TESTSUITE );

		require_once BASEDIR.'/server/services/wfl/WflLogOnService.class.php';
		$request = new WflLogOnRequest();
		$request->RequestInfo = []; // ticket only
		$request->User = $suiteOpts['User'];
		$request->Password = $suiteOpts['Password'];
		$request->Ticket = '';
		$request->Server = '';
		$request->ClientName = '';
		$request->Domain = '';
		$request->ClientAppName = 'IDS Speed Test';
		$request->ClientAppVersion = 'v'.SERVERVERSION;
		$request->ClientAppSerial = '';
		$request->ClientAppProductKey = '';

		/** @var WflLogOnResponse $response */
		$response = $this->callService( $request );
		$this->ticket = $response->Ticket;
	}

	public function logOff(): void
	{
		if( $this->ticket ) {
			require_once BASEDIR.'/server/services/wfl/WflLogOffService.class.php';
			$request = new WflLogOffRequest();
			$request->Ticket = $this->ticket;
			$this->callService( $request );
		}
		$this->ticket = null;
	}

	public function createWorkspace( int $layoutId, int $articleId ): void
	{
		$this->layoutId = $layoutId;
		$this->articleId = $articleId;

		require_once BASEDIR.'/server/services/wfl/WflCreateArticleWorkspaceService.class.php';
		$request = new WflCreateArticleWorkspaceRequest();
		$request->Ticket = $this->ticket;
		$request->ID = $this->articleId;
		$request->Format = 'application/incopyicml';
		$request->Content = null;

		/** @var WflCreateArticleWorkspaceResponse $response */
		$response = $this->callService( $request );
		$this->workspaceId = $response->WorkspaceId;
	}

	public function openArticleForEditing(): void
	{
		require_once BASEDIR .'/server/services/wfl/WflGetObjectsService.class.php';
		$request = new WflGetObjectsRequest();
		$request->Ticket = $this->ticket;
		$request->IDs = array( $this->articleId );
		$request->Lock = true;
		$request->Rendition = 'native';
		$request->RequestInfo = array( 'MetaData' );

		/** @var WflGetObjectsResponse $response */
		$response = $this->callService( $request );
		$this->article = $response->Objects[0];
		$this->tookArticleLock = true;

		$attachment = $this->getArticleNativeAttachment();
		require_once BASEDIR.'/server/utils/TransferClient.class.php';
		$transferClient = new WW_Utils_TransferClient( $this->ticket );
		if( $transferClient->downloadFile( $attachment ) ) {
			$transferClient->cleanupFile( $attachment );
		}
	}

	private function getArticleNativeAttachment(): Attachment
	{
		if( $this->article->Files ) foreach( $this->article->Files as $attachment ) {
			if( $attachment->Rendition === 'native' ) {
				return $attachment;
			}
		}
		throw new BizException( 'ERR_NOTFOUND', 'Server',
			'Could not find the native file of the article.' );
	}

	private function makeTextChangesInArticle(): string
	{
		require_once BASEDIR.'/server/utils/NumberUtils.class.php';
		$content = strval( $this->getArticleNativeAttachment()->Content );
		$count = 0;
		$content = str_replace( "{{RandomContent}}", NumberUtils::createGUID(), $content, $count );
		if( $count < 1 ) {
			throw new BizException( 'ERR_NOTFOUND', 'Server',
				'Could not find a {{RandomContent}} placeholder in the article content.' );
		}
		return $content;
	}

	public function checkInArticle(): void
	{
		if( $this->tookArticleLock ) {
			$attachment = $this->getArticleNativeAttachment();
			require_once BASEDIR.'/server/utils/TransferClient.class.php';
			$transferClient = new WW_Utils_TransferClient( $this->ticket );
			$transferClient->uploadFile( $attachment );

			require_once BASEDIR.'/server/services/wfl/WflSaveObjectsService.class.php';
			$request = new WflSaveObjectsRequest();
			$request->Ticket = $this->ticket;
			$request->Unlock = true;
			$request->CreateVersion = true;
			$request->ForceCheckIn = false;
			$request->Objects = array( $this->article );

			/** @var WflSaveObjectsResponse $response */
			$this->callService( $request );
		}
	}

	public function previewArticleAtWorkspace( int $editionId, bool $changeContent ): void
	{
		require_once BASEDIR.'/server/services/wfl/WflPreviewArticlesAtWorkspaceService.class.php';
		$request = new WflPreviewArticlesAtWorkspaceRequest();
		$request->Ticket = $this->ticket;
		$request->WorkspaceId = $this->workspaceId;
		$request->Action = 'Preview';
		$request->PreviewType = 'page';
		$request->Articles = [];
		$request->Articles[0] = new ArticleAtWorkspace();
		$request->Articles[0]->ID = $this->articleId;
		$request->Articles[0]->Format = 'application/incopyicml';
		$request->Articles[0]->Content = $changeContent ? $this->makeTextChangesInArticle() : null;
		$request->Articles[0]->Elements = null;
		$request->LayoutId = $this->layoutId;
		$request->EditionId = $editionId;

		$this->callService( $request );
	}

	public function unlockArticle(): void
	{
		if( !$this->tookArticleLock ) {
			return;
		}
		require_once BASEDIR.'/server/services/wfl/WflUnlockObjectsService.class.php';
		$request = new WflUnlockObjectsRequest();
		$request->Ticket = $this->ticket;
		$request->IDs = [ $this->articleId ];

		/** @var WflUnlockObjectsResponse $response */
		$response = $this->callService( $request );
		if( $response->Reports ) foreach( $response->Reports as $report ) {
			if( $report->Entries ) foreach( $report->Entries as $entry ) {
				if( $entry->MessageLevel === 'ERROR' ) {
					throw new BizException( null, 'Server', $entry->Details, $entry->Message );
				}
			}
		}
		$this->tookArticleLock = false;
	}

	public function closeWorkspace(): void
	{
		if( $this->workspaceId ) {
			require_once BASEDIR.'/server/services/wfl/WflDeleteArticleWorkspaceService.class.php';
			$request = new WflDeleteArticleWorkspaceRequest();
			$request->Ticket = $this->ticket;
			$request->WorkspaceId = $this->workspaceId;
			$this->callService( $request );
		}
		$this->workspaceId = null;
	}

	/**
	 * Executes a service request (through a JSON client).
	 *
	 * @param mixed $request Request object to execute.
	 * @param string|null $expectedSCode Expected server error (S-code). Use null to indicate no error is expected.
	 * @return mixed Response object.
	 * @throws BizException when the web service failed.
	 */
	private function callService( object $request, ?string $expectedSCode = null ): object
	{
		$requestClass = get_class( $request ); // e.g. 'WflSaveObjectsRequest'
		$webInterface = substr( $requestClass, 0, 3 );
		$funtionNameLen = strlen( $requestClass ) - strlen( $webInterface ) - strlen( 'Request' );
		$functionName = substr( $requestClass, strlen( $webInterface ), $funtionNameLen );

		require_once BASEDIR.'/server/protocols/json/'.$webInterface.'Client.php';
		$clientClass = 'WW_JSON_'.$webInterface.'Client';
		$options = array();
		if( $expectedSCode ) {
			$options['expectedError'] = $expectedSCode;
		}
		try {
			$client = new $clientClass( '', $options );
			$response = $client->$functionName( $request );
		} catch( Exception $e ) {
			throw new BizException( '', 'Server', '', $e->getMessage() );
		}
		return $response;
	}
}