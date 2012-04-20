<?php

$io->tell(
<<<EOH
Usage : {$io->getScriptPath()} [options] url

Options :
    -f, --force
        Force extensions detection using static files.
    
    -h, --help
        Show this help.
    
    -i, --investigate=LEVEL
        Set the investigation level :
            0 : only look for meta-data, changelogs and listable directories
            1 : 0 + version detection using fingerprints
            2 : 1 + plugins enumeration
            3 : 2 + plugins version detection using fingerprints (default)
       
    -p, --proxy=IP:PORT
        Use a proxy.
            
EOH
);
exit(0);