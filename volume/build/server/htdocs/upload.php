<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Server\Config;
use Server\Page;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Upload / Import');
$page->addScript("/js/select2.min.js");
$page->addCss('
<link href="/css/select2.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Bootstrap styles -->
    <!-- Generic page styles -->
    <style>
      @media (max-width: 767px) {
        .description {
          display: none;
        }
      }
    /* jquery.fileupload bootstrap4 fix */ 
    .fade.in {
        opacity: 1
    }
    /* bootstrap4 modal fix */
    .modal-backdrop {
        /* bug fix - no overlay */    
        display: none;    
    }
    .modal {
        z-index: 10001;
    }
    .btn {
        font-size: 0.8rem;
    }
    
#dropzone {
    background: #c6fabd;
    width: 400px;
    height: 100px;
    line-height: 100px;
    text-align: center;
    font-weight: bold;
    border-radius: 25px;
}
#dropzone.in {
    width: 600px;
    height: 200px;
    line-height: 200px;
    font-size: larger;
    border-radius: 25px;
}
#dropzone.hover {
    background: #b2e1aa;
}
#dropzone.fade {
    -webkit-transition: all 0.3s ease-out;
    -moz-transition: all 0.3s ease-out;
    -ms-transition: all 0.3s ease-out;
    -o-transition: all 0.3s ease-out;
    transition: all 0.3s ease-out;
    opacity: 1;
}    
    
    </style>
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="/css/jquery.fileupload/jquery.fileupload.css" />
    <link rel="stylesheet" href="/css/jquery.fileupload/jquery.fileupload-ui.css" />
    <!-- CSS adjustments for browsers with JavaScript disabled -->
    <noscript
      ><link rel="stylesheet" href="/css/jquery.fileupload/jquery.fileupload-noscript.css"
    /></noscript>
    <noscript
      ><link rel="stylesheet" href="/css/jquery.fileupload/jquery.fileupload-ui-noscript.css"
    /></noscript>
');

$page->showHeader('import');

$set = $page->getRequest()->getQueryParam('set', '');
$statsTab = $page->getRequest()->getCookieParam('statsTab', 'tab-1');

if ($set != '') {
    echo '<h4>File Upload <em>'.htmlspecialchars($set).'</em> <span class="fas fa-info-circle"></span></h4>'.PHP_EOL;
} else {
    echo '<h4>Upload and import articles ' . $page->info('upload') . '</h4>';
}
?>

      <div class="container">
      <!-- The file upload form used as target for the file upload widget -->
      <form
        id="fileupload"
        action="/upload/index.php"
        method="POST"
        enctype="multipart/form-data"
      >
        <!-- Redirect browsers with JavaScript disabled to the origin page -->
        <noscript
          ><input
            type="hidden"
            name="redirect"
            value="/upload/index.php"
        /></noscript>


        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar" style="margin-top: 30px">
          <div class="col-sm-12">
            <!-- The fileinput-button span is used to style the file input field as button -->
            <span class="btn btn-success fileinput-button">
              <span class="fas fa-plus"></span>
              <span>Add files...</span>
              <input type="file" name="files[]" multiple />
            </span>
            <button type="submit" class="btn btn-primary start">
              <i class="fas fa-upload"></i>
              <span>Start upload</span>
            </button>
            <button type="reset" class="btn btn-warning cancel">
              <i class="fas fa-ban"></i>
              <span>Cancel upload</span>
            </button>
            <button type="button" class="btn btn-danger delete">
              <i class="fas fa-trash"></i>
              <span>Delete selected</span>
            </button>
            <input type="checkbox" class="toggle" />
            <!-- The global file processing state -->
            <span class="fileupload-process"></span>
          </div>
        </div>
        <div>
          <!-- The global progress state -->
          <div class="col-lg-5 fileupload-progress fade">
            <!-- The global progress bar -->
            <div
              class="progress progress-striped active"
              role="progressbar"
              aria-valuemin="0"
              aria-valuemax="100"
            >
              <div
                class="progress-bar progress-bar-success"
                style="width:0%;"
              ></div>
            </div>
            <!-- The extended global progress state -->
            <div class="progress-extended">&nbsp;</div>
          </div>
        </div>

        <select id="destset" name="destset" class="js-data-get-sets" style="width: 400px"></select> <?=$page->info('upload-select', 0.9) ?>

        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped" style="margin-top: 20px">
          <tbody class="files"></tbody>
        </table>
      </form>
    </div>
    <!-- The template to display files available for upload -->
    <script id="template-upload" type="text/x-tmpl">
      {% for (var i=0, file; file=o.files[i]; i++) { %}
          <tr class="template-upload fade">
              <td>
                  <span class="preview"></span>
              </td>
              <td>
                  {% if (window.innerWidth > 480 || !o.options.loadImageFileTypes.test(file.type)) { %}
                      <p class="name">{%=file.name%}</p>
                  {% } %}
                  <strong class="error text-danger"></strong>
              </td>
              <td>
                  <p class="size">Processing...</p>
                  <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
              </td>
              <td>
                  {% if (!o.options.autoUpload && o.options.edit && o.options.loadImageFileTypes.test(file.type)) { %}
                    <button class="btn btn-success edit" data-index="{%=i%}" disabled>
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </button>
                  {% } %}
                  {% if (!i && !o.options.autoUpload) { %}
                      <button class="btn btn-primary start" disabled>
                          <i class="fas fa-upload"></i>
                          <span>Start</span>
                      </button>
                  {% } %}
                  {% if (!i) { %}
                      <button class="btn btn-warning cancel">
                          <i class="fas fa-ban"></i>
                          <span>Cancel</span>
                      </button>
                  {% } %}
              </td>
          </tr>
      {% } %}
    </script>
    <!-- The template to display files available for download -->
    <script id="template-download" type="text/x-tmpl">
      {% for (var i=0, file; file=o.files[i]; i++) { %}
          <tr class="template-download fade">
              <td>
                  <span class="preview">
                      {% if (file.thumbnailUrl) { %}
                          <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                      {% } %}
                  </span>
              </td>
              <td>
                  {% if (window.innerWidth > 480 || !file.thumbnailUrl) { %}
                      <p class="name">
                          {% if (file.url) { %}
                              <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                                 <!-- subdirs inside zipfile -->
                                {% if (file.subDirs) { %}
                                <p>
                                    {% for (var a=0, subDir; subDir=file.subDirs[a]; a++) { %}
                                        {%=subDir%}<br />
                                    {% } %}
                                </p>
                                {% } %}
                          {% } else { %}
                              <span>{%=file.name%}</span>
                          {% } %}
                      </p>
                  {% } %}
                  {% if (file.error) { %}
                      <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                  {% } %}
              </td>
              <td>
                  <span class="size">{%=o.formatFileSize(file.size)%}</span>
              </td>
              <td>
                  {% if (file.deleteUrl) { %}
                      <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                          <i class="fas fa-trash"></i>
                          <span>Delete</span>
                      </button>
                      <input type="checkbox" name="delete" value="1" class="toggle">
                  {% } else { %}
                      <button class="btn btn-warning cancel">
                          <i class="fas fa-ban"></i>
                          <span>Cancel</span>
                      </button>
                  {% } %}
                      <button class="btn btn-info delete" data-type="{%=file.importType%}" data-url="{%=file.importUrl%}">
                          <i class="fas fa-wrench"></i>
                          <span>Import</span>
                      </button>
              </td>
          </tr>
      {% } %}


    </script>

    <div id="dropzone" class="fade well">Drop files here</div>






    <!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
    <script src="js/jquery.fileupload/vendor/jquery.ui.widget.js"></script>
    <!-- The Templates plugin is included to render the upload/download listings -->
    <script src="/js/javascript-templates/tmpl.min.js"></script>
    <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
    <script src="https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>
    <!-- The Canvas to Blob plugin is included for image resizing functionality -->
    <script src="https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
    <!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
    <!-- blueimp Gallery script -->
    <script src="https://blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
    <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
    <script src="js/jquery.fileupload/jquery.iframe-transport.js"></script>
    <!-- The basic File Upload plugin -->
    <script src="js/jquery.fileupload/jquery.fileupload.js"></script>
    <!-- The File Upload processing plugin -->
    <script src="js/jquery.fileupload/jquery.fileupload-process.js"></script>
    <!-- The File Upload image preview & resize plugin -->
    <script src="js/jquery.fileupload/jquery.fileupload-image.js"></script>
    <!-- The File Upload audio preview plugin -->
    <script src="js/jquery.fileupload/jquery.fileupload-audio.js"></script>
    <!-- The File Upload video preview plugin -->
    <script src="js/jquery.fileupload/jquery.fileupload-video.js"></script>
    <!-- The File Upload validation plugin -->
    <script src="js/jquery.fileupload/jquery.fileupload-validate.js"></script>
    <!-- The File Upload user interface plugin -->
    <script src="js/jquery.fileupload/jquery.fileupload-ui.js"></script>
    <!-- The main application script -->
    <script src="js/jquery.fileupload/demo.js"></script>
    <!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
    <!--[if (gte IE 8)&(lt IE 10)]>
      <script src="js/cors/jquery.xdr-transport.js"></script>
    <![endif]-->
    <script>
        var debugUpload = false;
        $('#myModal').modal('hide');

        $(document).bind('drop dragover', function (e) {
            // Prevent the default browser drop action:
            e.preventDefault();
            return false;
        });

        $('#fileupload').fileupload({
            dropZone: $('#dropzone')
        });

        /*
         * Triggered after upload completed
         * Helps to debug problems
         */
        $('#fileupload')
            .bind('fileuploadalways', function (e, data) {
                if (debugUpload) {
                    alert('.bind[\'fileuploadalways\']: ' + JSON.stringify(data.result) );
                }
            });

        /*
         * Triggered when Delete or Import button is pressed.
         * Adds destination set to Import button.
         */
        $('#fileupload')
            .bind('fileuploaddestroy', function (e, data) {
                if (debugUpload) {
                    alert('.bind[\'fileuploaddestroy\']: ' + JSON.stringify(data.result) );
                }
                /* data.type == DELETE: DELETE action
                   data.type == POST: IMPORT action
                 */
                if (data.type === 'POST') {
                    var select2 = $('#destset');
                    var destset = select2.val();
                    if (destset === null) {
                        destset = 'main';
                    }
                    showMessage('Importing to ' + destset + '...',
                            '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Scanning and importing...</span></div></div>',
                            ''
                    );
                    data.url += '&_destset=' + destset;
                }
            });

        /*
         * Triggered after return of result of Delete / Import buttons.
         */
        $('#fileupload')
            .bind('fileuploaddestroyed', function (e, data) {
                if (debugUpload) {
                    alert('.bind[\'fileuploaddestroyed\']: ' + JSON.stringify(data.result));
                }
                var message = '';
                var msgClass = '';
                if (data.result && data.result['isImport']) {
                    if (data.result['message']) {
                        message += '<h5>' + data.result['message'] + '</h5>';
                    }
                    if (data.result['documentsImported']) {
                        message += data.result['documentsImported'] + " article" + (data.result['documentsImported'] != 1 ? 's' : '') + " imported to set <em>" + data.result['destSet'] + "</em>.";
                        msgClass = 'success';
                    } else {
                        message += "No articles have been imported.";
                        msgClass = 'warning';
                    }
                    message += '<br />';
                    var text = ['Not found', 'no tex file found', 'texfile exists', 'texfile added', 'directory exists', 'move directory error'];
                    if (data.result['files'] !== undefined) {
                        var subDirs = data.result['files'];
                        message += '<small>';
                        for (var prop in subDirs) {
                            // if string, a file had been added
                            if (typeof subDirs[prop] === 'string' || subDirs[prop] instanceof String) {
                                message += prop + ": " + 'texfile ' + subDirs[prop] + ' added';
                            } else {
                                message += prop + ": " + text[subDirs[prop]]
                            }
                            message += '.<br />';
                        }
                        message += '</small>';
                    }
                    var title = 'Article Import to set <em>' + data.result['destSet'] + '</em>.';
                    showMessage(title, message, msgClass, 10000);
                }
            });

        $('#dropzone').bind('dragenter', function (e) {
            var dropZone = $('#dropzone');
            dropZone.addClass('in');
            var hoveredDropZone = $(e.target).closest(dropZone);
            dropZone.addClass('hover', hoveredDropZone.length);
        });

        $('#dropzone').bind('dragleave', function (e) {
                var dropZone = $('#dropzone');
                dropZone.removeClass('in hover');
        });

        $(document).bind('drop', function (e) {
            var dropZone = $('#dropzone');
            if (dropZone.hasClass('in')) {
                dropZone.removeClass('in hover');
            }
        });

    </script>
<?php

$deferJS[] = '
$(".js-data-get-sets").select2({
    tags: true,
    placeholder: "Please specify a set when you import",
    ajax: {
    url: "/ajax/getSets.php",
    dataType: "json"
    }
});';


$page->showFooter($deferJS);
