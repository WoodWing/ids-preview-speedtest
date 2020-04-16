<?php declare(strict_types=1);

exit ( ( new WW_WwTest_IdsSpeedTest_Cli() )->handle() );

class WW_WwTest_IdsSpeedTest_Cli
{
	public function handle(): int
	{
		$exitCode = 1; // assume error
		try {
			require_once __DIR__.'/../../../config/config.php';
			require_once __DIR__.'/config/config.php';
			list( $idsVersion, $scenarioName, $iteration ) = $this->takeCliArguments();
			if( $iteration === 0 ) {
				$this->initReport();
			} else {
				$this->runTestCommand( $idsVersion, $scenarioName, $iteration );
			}
			$exitCode = 0; // success
		} catch( BizException $e ) {
			$this->stdErrMessage( $e->getMessage().' '.$e->getDetail() );
		} catch( Throwable $e ) {
			$this->stdErrMessage( $e->getMessage() );
		}
		return $exitCode;
	}

	private function takeCliArguments(): array
	{
		$cliArguments = getopt( '', [ 'idsversion:', 'scenario:', 'iteration:' ] );
		$idsVersion = strval( $cliArguments['idsversion'] ?? '' );
		if( !$idsVersion ) {
			throw new BizException( 'ERR_ARGUMENT', 'Client', '"idsversion" parameter missing.' );
		}
		$iteration = intval( $cliArguments['iteration'] ?? 0 );
		if( $iteration < 0 ) {
			throw new BizException( 'ERR_ARGUMENT', 'Client', '"iteration" parameter missing.' );
		}
		$scenarioName = strval( $cliArguments['scenario'] ?? '' );
		if( $iteration > 0 && !$scenarioName ) {
			throw new BizException( 'ERR_ARGUMENT', 'Client', '"scenario" parameter missing.' );
		}
		return [ $idsVersion, $scenarioName, $iteration ];
	}

	private function initReport(): void
	{
		$report = new WW_WwTest_IdsSpeedTest_Report();
		$report->openReport();
		$report->saveHeader( ( new WW_WwTest_IdsSpeedTest_Runner() )->getScenarioSteps() );
		$report->closeReport();
	}

	private function runTestCommand( string $idsVersion, string $scenarioName, int $iteration ): void
	{
		$report = new WW_WwTest_IdsSpeedTest_Report();
		$report->openReport();
		foreach( ( new WW_WwTest_IdsSpeedTest_UseCases( $idsVersion ) )->getUseCases() as $useCase ) {
			if( $scenarioName === $useCase->getName() ) {
				$monitor = ( new WW_WwTest_IdsSpeedTest_Runner() )->runScenarioForUseCase( $useCase, $iteration );
				$report->saveScenario( $useCase, $monitor );
			}
		}
		$report->closeReport();
	}

	private function stdErrMessage( string $message ): void
	{
		fwrite(STDERR, 'ERROR: '.$message.PHP_EOL );
	}
}
