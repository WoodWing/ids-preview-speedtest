<?php declare( strict_types=1 );

class WW_WwTest_IdsSpeedTest_UseCases
{
	/** @var string */
	private $idsVersion;

	public function __construct( string $idsVersion )
	{
		$this->idsVersion = $idsVersion;
		if( !array_key_exists( $this->idsVersion, unserialize( ADOBE_VERSIONS ) ) ) {
			throw new BizException( 'ERR_ARGUMENT', 'Client',
				'InDesign Server version is not supported: '.$this->idsVersion );
		}
	}

	/**
	 * @return WW_WwTest_IdsSpeedTest_UseCase[]
	 */
	public function getUseCases(): array
	{
		static $testCases;
		if( !isset( $testCases ) ) {
			$testCases = $this->readUseCases();
		}
		return $testCases;
	}

	/**
	 * @return WW_WwTest_IdsSpeedTest_UseCase[]
	 */
	private function readUseCases(): array
	{
		$testScenarios = [];
		foreach( $this->readUseCasesFromJson() as $testScenario ) {
			$testScenarios[] = new WW_WwTest_IdsSpeedTest_UseCase(
				$testScenario->Name, $testScenario->Description, intval( $testScenario->ArticleId ),
				intval( $testScenario->LayoutId ), intval( $testScenario->EditionId ) );
		}
		return $testScenarios;
	}

	private function readUseCasesFromJson() : array
	{
		$filePath = __DIR__."/input/{$this->idsVersion}/usecases.json";
		$config = file_get_contents( $filePath );
		if( $config === false ) {
			throw new BizException( 'ERR_ERROR', 'Server',
				'Configuration file missing: '.$filePath );
		}
		$json = json_decode( $config );
		if( $json === null ) {
			throw new BizException( 'ERR_ERROR', 'Server',
				'JSON in configuration file could not be parsed: '.$filePath );
		}
		if( !$json->UseCases ) {
			throw new BizException( 'ERR_ERROR', 'Server',
				'No Scenarios item found at root level of: '.$filePath );
		}
		return $json->UseCases;
	}
}