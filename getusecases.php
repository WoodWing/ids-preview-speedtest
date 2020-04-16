<?php declare( strict_types=1 );

require_once __DIR__.'/../../../config/config.php';
$cliArguments = getopt( '', [ 'idsversion:' ] );
$idsVersion = strval( $cliArguments['idsversion'] ?? '' );
$names = [];
foreach( ( new WW_WwTest_IdsSpeedTest_UseCases( $idsVersion ) )->getUseCases() as $useCase ) {
	$names[] = $useCase->getName();
}
echo implode( ',', $names );