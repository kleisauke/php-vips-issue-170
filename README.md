SEGFAULT Reproduction
---------------------

This issue only arises after the addition of `FFI::shutdown()` in index.php however if you don't call the shutdown function the memory used by the container accumulates - usually until it is killed.

`docker build -t libvips-segfault-repro .`

`docker run -it --rm -p 8000:80 libvips-segfault-repro`

Run apachebench to make some volume of requests _or_ just keep hitting refresh in a browser
`ab -n 100 -c 5 http://localhost:8000/`

Watch the output from the container and observe the SEGFAULTS

---
If you want to run the container with the local volume mounted run
`composer install` and mount the current working dir to `/var/www`

`docker run -it --rm -v $PWD:/var/www -p 8000:80 libvips-segfault-repro`
