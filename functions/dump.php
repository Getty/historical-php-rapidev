<?php
function dump($Data, $force=false) {
        if(STAGE === 'dev' || $force === true || 'true' == RD::$Self->GetGet('debugausgabe')){
                RDD::EchoDebugDump($Data);
        }
}
