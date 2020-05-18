# texmlbus

Texmlbus (Tex to XML BUild System) supports the conversion process of documents written in LaTeX. Documents can not only be converted to pdf, but also to other output formats – such as markup languages like html. In particular, conversion to XML and MathML is supported via [LaTeXML](https://dlmf.nist.gov/LaTeXML/). texmlbus can schedule jobs among several workers (possibly on different hosts), extracts and analyzes the conversion process of each document and stores results in its own database. Result documents as well as statistics about the results of the build process can be easily retrieved using a web browser.

## Getting Started

The whole system runs inside docker containers.

Download [Docker Desktop](https://www.docker.com/products/docker-desktop) for Mac or Windows. [Docker Compose](https://docs.docker.com/compose) will be automatically installed. On Linux, make sure you have the latest version of [Compose](https://docs.docker.com/compose/install/).

```
1. git clone --recursive https://github.com/stamer/texmlbus.git
2. cd texmlbus
3. git submodule update --init --recursive
4. docker-compose up
5. # Please be patient when the system is installed the very first time, as a full 
   # TeXLive system will be installed. Later the system will startup much faster.
6. # The system will continue after displaying OK: 3xxx MiB in 1xx packages. Please be patient.
7. # Go to http://localhost:8080 or https://localhost:8443
8. # press Ctrl-C to stop the system.
```

## Usage

Start the app as described above. Upload LaTeX documents via a web browser and convert documents.

## Documentation

Further documentation can be found via the menu once the project is started. 

## Sponsors

Many thanks to the great people at [Overleaf](https://www.overleaf.com) for sponsoring this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.




