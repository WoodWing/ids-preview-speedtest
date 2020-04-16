<?php declare( strict_types=1 );

class WW_WwTest_IdsSpeedTest_Report
{
	/** @var false|resource */
	private $csvFileHandle;

	public function openReport()
	{
		$this->csvFileHandle = fopen( "php://output", 'a' );
		$utf8BomMarker = ( chr( 0xEF ).chr( 0xBB ).chr( 0xBF ) );
		fputs( $this->csvFileHandle, $utf8BomMarker );
	}

	public function saveHeader( array $testSteps ): void
	{
		$this->addFieldsToReport( array_merge( [ 'Scenario', 'Description' ], $testSteps, [ 'Total' ] ) );
	}

	public function saveScenario( WW_WwTest_IdsSpeedTest_UseCase $scenario, WW_WwTest_IdsSpeedTest_Monitor $monitor ): void
	{
		$fields = [ $scenario->getName(), $scenario->getDescription() ];
		foreach( $monitor->getSteps() as $testStep ) {
			$fields[] = $this->durationToString( $monitor->getDurationForStep( $testStep ) );
		}
		$fields[] = $this->durationToString( $monitor->getTotalDuration() );
		$this->addFieldsToReport( $fields );
	}

	private function addFieldsToReport( array $fields ): void
	{
		fputcsv( $this->csvFileHandle, $fields, IDS_SPEED_TEST_CSV_FIELD_DELIMITER );
	}

	public function closeReport(): void
	{
	}

	private function durationToString( float $timeStamp ): string
	{
		$timeInSec = intval( floor( $timeStamp ) ); // whole seconds since Unix Epoch
		$miliSec = intval( floor( ( $timeStamp - $timeInSec ) * 1000 ) ); // range [0...999]
		return $timeInSec.IDS_SPEED_TEST_CSV_DECIMAL_SEPARATOR.sprintf( '%03d', $miliSec );
	}
}