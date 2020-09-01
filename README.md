# CFD Explorer

A tool for a more efficient visualisation of CFD flights (French Paragliding Championship).

## Working deployments
The server-side code has been deployed on:
- Raspberry Pi3 / Raspbian / NGINX / PHP7 FPM
- Ubuntu x86  / Apache2 / Mod-PHP7

## Dependencies
Some PHP dependencies:
- php7.0-json
- php7.0-readline
- php7.0-xml
- php7.0-opcache

## Installation guide
You'll figure it out.

## Kown issues
### CFD Output
CFD output format of '/cfd/selectionner-les-vols' is just horrible:
- 3 different formats, depending on some strange conditions
- Not really sometimes the announced format
- 90's style

> Support for (not really) doc is missing, so 1 result output seems not to be supported

### Memory consumption
> Large number of results can make the CGI to run out of memory on the server

### GUI
GUI might be buggy sometimes