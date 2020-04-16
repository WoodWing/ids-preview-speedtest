<?php declare( strict_types=1 );

class WW_WwTest_IdsSpeedTest_Runner
{
	/** @var WW_WwTest_IdsSpeedTest_WebEditProxy */
	private $serviceProxy;

	/** @var WW_WwTest_IdsSpeedTest_UseCase */
	private $scenario;

	/** @var WW_WwTest_IdsSpeedTest_Monitor */
	private $monitor;

	/** @var int */
	private $iteration;

	/** @var string[] */
	private $scenarioSteps = [
		'Open art',
		'Preview#1',
		'Edit art',
		'Preview#2',
		'Save art',
		'Preview#3'
	];

	public function __construct()
	{
		$this->serviceProxy = new WW_WwTest_IdsSpeedTest_ServiceProxy();
	}

	public function getScenarioSteps(): array
	{
		return $this->scenarioSteps;
	}

	public function runScenarioForUseCase( WW_WwTest_IdsSpeedTest_UseCase $useCase, int $iteration ): WW_WwTest_IdsSpeedTest_Monitor
	{
		$this->monitor = new WW_WwTest_IdsSpeedTest_Monitor();
		$this->scenario = $useCase;
		$this->iteration = $iteration;
		$this->serviceProxy->logOn();
		try {
			$this->runPreviewScenario();
		} finally {
			$this->serviceProxy->logOff();
		}
		return $this->monitor;
	}

	private function runPreviewScenario(): void
	{
		try {
			$this->openArticleAndPreview();
			$this->changeArticleAndPreview();
			$this->saveArticleAndPreview();
		} finally {
			$this->serviceProxy->unlockArticle();
			$this->serviceProxy->closeWorkspace();
		}
	}

	private function openArticleAndPreview(): void
	{
		$this->monitor->newRecording( $this->scenarioSteps[0] );
		$this->serviceProxy->createWorkspace( $this->scenario->getLayoutId(), $this->scenario->getArticleId() );
		$this->serviceProxy->openArticleForEditing();
		$this->monitor->endRecording();

		$this->monitor->newRecording( $this->scenarioSteps[1] );
		$this->serviceProxy->previewArticleAtWorkspace( $this->scenario->getEditionId(), false );
		$this->monitor->endRecording();
	}

	private function changeArticleAndPreview(): void
	{
		$this->monitor->newRecording( $this->scenarioSteps[2] );
		// nothing to do here as changed content will be passed on during preview in next step
		$this->monitor->endRecording();

		$this->monitor->newRecording( $this->scenarioSteps[3] );
		$this->serviceProxy->previewArticleAtWorkspace( $this->scenario->getEditionId(), true );
		$this->monitor->endRecording();
	}

	private function saveArticleAndPreview(): void
	{
		$this->monitor->newRecording( $this->scenarioSteps[4] );
		$this->serviceProxy->checkInArticle();
		$this->monitor->endRecording();

		$this->monitor->newRecording( $this->scenarioSteps[5] );
		$this->serviceProxy->previewArticleAtWorkspace( $this->scenario->getEditionId(), false );
		$this->monitor->endRecording();
	}
}