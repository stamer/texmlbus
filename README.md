# texmlbus

Texmlbus (Tex to XML BUild System) supports the conversion process of documents written in LaTeX. Documents can not only be converted to pdf, but also to other output formats – such as markup languages like html. In particular, conversion to XML, HTML and MathML is supported via [LaTeXML](https://dlmf.nist.gov/LaTeXML/). texmlbus can schedule jobs among several workers (possibly on different hosts), extracts and analyzes the conversion process of each document and stores results in its own database. Result documents as well as statistics about the results of the build process can be easily retrieved using a web browser.

## Getting Started

The whole system runs inside docker containers.

Download [Docker Desktop](https://www.docker.com/products/docker-desktop) for Mac or Windows. [Docker Compose](https://docs.docker.com/compose) will be automatically installed. On Linux, make sure you have the latest version of [Compose](https://docs.docker.com/compose/install/).

```
1. git clone https://github.com/stamer/texmlbus.git
2. cd texmlbus
3. docker-compose up
5. # Please be patient when images are downloaded the very first time, as a full 
   # TeXLive system will be installed. Later the system will startup much faster.
6. # Go to http://localhost:8080 or https://localhost:8443
7. # press Ctrl-C to stop the system.
```

If you would like to develop with latexml, you can also build the images yourself and update the images after changes.

```
1. git clone --recursive https://github.com/stamer/texmlbus.git
2. cd texmlbus
3. git submodule update --init --recursive
4. docker-compose -f docker-compose.yml -f docker-compose-build.yml build
   # `docker compose build` (v2, without hyphen) does not currently work, use `build-images.bat` 
   # or `build-images.sh` then.
   # see issues for explanation.
   # Please be patient when the system is installed the very first time, as a full 
   # TeXLive system will be installed. Later the system will startup much faster.
   # The system will continue after displaying OK: 3xxx MiB in 1xx packages. Please be patient.
5. docker-compose up
6. # Go to http://localhost:8080 or https://localhost:8443
7. # press Ctrl-C to stop the system.
```

## Run several workers
In order to run several (here three) workers in parallel enter
```
docker-compose up --scale latexml_dmake=3
```

## Usage

Install and start the app as described above.

1. Goto http://localhost:8080
2. Click on <a href="https://github.com/stamer/texmlbus/wiki/images/texmlbus_home.png" target="_texmlbus">__Create Sample Set__</a> in the top menu.
2. Click on <a href="https://github.com/stamer/texmlbus/wiki/images/texmlbus_create_samples.png" target="_texmlbus">__Create Samples__</a> button.
3. Click on <a href="https://github.com/stamer/texmlbus/wiki/images/texmlbus_scan_samples.png" target="_texmlbus">__Scan samples directory__</a> button to import some sample documents
4. Goto <a href="https://github.com/stamer/texmlbus/wiki/images/texmlbus_documents_start.png" target="_texmlbus">Documents alphabetically</a> to see the documents.
5. Click on <a href="https://github.com/stamer/texmlbus/wiki/images/texmlbus_sample_documents.png" target="_texmlbus">__queue__</a> to convert a document.
6. Click on <a href="https://github.com/stamer/texmlbus/wiki/images/texmlbus_sample_documents.png" target="_texmlbus">__Destfile__</a> to see the converted document.

## Upgrade ##

If you upgrade from previous versions, you might want to adapt your makefiles to the new source code layout. Go to `Import / Manage` --> Fix Makefiles and click on the button there. 

## Documentation

The [wiki](https://github.com/stamer/texmlbus/wiki) contains how-tos and provides help if you have any issues using texmlbus.

Any feedback is welcome, you can use the the [issues](https://github.com/stamer/texmlbus/issues) page for that.

## Sponsors

Many thanks to the great people at [Overleaf](https://www.overleaf.com) for sponsoring this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.



