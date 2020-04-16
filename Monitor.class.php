<?php declare( strict_types=1 );

class WW_WwTest_IdsSpeedTest_Monitor
{
	/** @var string */
	private $testStep;

	public function newRecording( string $testStep ): void
	{
		$this->testStep = $testStep;
		$this->startTime = microtime( true );
	}

	public function endRecording(): void
	{
		$this->durations[ $this->testStep ] = microtime( true ) - $this->startTime;
	}

	public function getDurationForStep( string $testStep ): float
	{
		return $this->durations[ $testStep ];
	}

	public function getTotalDuration(): float
	{
		return array_sum( $this->durations );
	}

//	public function getAverageDurationForStep( string $testStep ): float
//	{
//		$durations = $this->durations[ $testStep ];
//		$count = count( $durations );
//		if( $count >= 5 ) {
//			return ( array_sum( $durations ) - max( $durations ) - min( $durations ) ) / ( $count - 2 );
//		}
//		return $count ? array_sum( $durations ) / $count : 0;
//	}
//
//	public function getTotalDuration(): float
//	{
//		$sum = 0;
//		foreach( array_keys( $this->durations ) as $testStep ) {
//			$sum += $this->getAverageDurationForStep( $testStep );
//		}
//		return $sum;
//	}

	public function getSteps(): array
	{
		return array_keys( $this->durations );
	}
}