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

### Typical issues using docker ### 

#### Windows
- On Windows try to use wsl2. It is faster and provides all the memory of the host machine. 
  If you use wsl2 and receive an error message like this 
  ```
  docker.credentials.errors.InitializationError: docker-credential-desktop.exe not installed or 
  not available in PATH [399] Failed to execute script docker-compose
  ```
  when using `docker-compose up`, do
  ```bash
    echo 'export PATH=$PATH:"/mnt/c/Program Files/Docker/Docker/resources/bin"' >> $HOME/.bashrc 
  ```
  inside the wsl console. Exit wsl and enter the wsl console again.

- __Avoid share drive questions__
  
  `Docker → Settings → Resources → File Sharing`
  
  Add the folder `texmlbus`.
  
- __Unable to share drive__
  On windows you will be asked to share a drive. Please click ”Share it”. Also the windows firewall might ask you to allow a     connection.

  If you still receive error messages like unable to share drive, then a problem might be, that SMB is not enabled on your  machine.
  To enable SMB2 on Windows 10, you need to press the Windows Key + S and start typing and click on Turn Windows features on or off. You can also search the same phrase in Start, Settings. Scroll down to SMB 1.0/CIFS File Sharing Support and check that top box.
- __Need more memory__
  If you are not using wsl2 enabled Docker on Windows, you need to assign memory via 

  `Docker →→ Settings →→ Resources` 

  Increase Memory on the right side. It is worth it. If possible switch to wsl2 on Windows.

  

Further documentation can be found via the menu once the project is started. 

## Sponsors

Many thanks to the great people at [Overleaf](https://www.overleaf.com) for sponsoring this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.




