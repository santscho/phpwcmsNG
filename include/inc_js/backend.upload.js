/*
 * Backend multi file upload
 *
 * Posts every selected file to include/inc_act/act_upload.php, one request per
 * file and under the field name the upload handler expects (qqfile), then
 * reloads the listing so the uploaded files can be taken over. The endpoint,
 * its CSRF token and its JSON response are unchanged.
 */
document.addEventListener('DOMContentLoaded', function () {

    var dropzone = document.querySelector('[data-upload-dropzone]');
    var input = document.querySelector('[data-upload-input]');

    if (!dropzone || !input) {
        return;
    }

    var list = document.querySelector('[data-upload-list]');
    var action = input.getAttribute('data-upload-action');
    var doneText = input.getAttribute('data-upload-done') || 'OK';
    var failedText = input.getAttribute('data-upload-failed') || 'failed';

    var queue = 0;
    var uploaded = 0;

    function addRow(filename) {

        var item = document.createElement('div');
        item.className = 'list-group-item d-flex align-items-center gap-2';

        var name = document.createElement('span');
        name.className = 'flex-grow-1 text-truncate';
        name.textContent = filename;

        var state = document.createElement('span');
        state.className = 'badge theme-secondary flex-shrink-0';
        state.textContent = '...';

        item.appendChild(name);
        item.appendChild(state);
        list.appendChild(item);
        list.classList.remove('d-none');

        return state;

    }

    function setState(state, type, text) {
        state.className = 'badge theme-' + type + ' flex-shrink-0';
        state.textContent = text;
    }

    function finished() {

        if (queue) {
            return;
        }

        if (uploaded) {
            window.location.reload();
            return;
        }

        input.value = '';

    }

    function upload(file) {

        var state = addRow(file.name);
        var form = new FormData();
        var request = new XMLHttpRequest();

        form.append('qqfile', file, file.name);

        queue++;

        request.upload.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
                state.textContent = Math.round(event.loaded / event.total * 100) + '%';
            }
        });

        request.addEventListener('load', function () {

            var response = {};
            var json = request.responseText.match(/\{[\s\S]*\}\s*$/);

            try {
                response = json ? JSON.parse(json[0]) : {};
            } catch (error) {
                response = {};
            }

            queue--;

            if (response.success) {
                uploaded++;
                setState(state, 'success', doneText);
            } else {
                setState(state, 'danger', response.error || failedText);
            }

            finished();

        });

        request.addEventListener('error', function () {
            queue--;
            setState(state, 'danger', failedText);
            finished();
        });

        request.open('POST', action, true);
        request.send(form);

    }

    function queueFiles(files) {
        Array.prototype.forEach.call(files, upload);
    }

    input.addEventListener('change', function () {
        if (input.files.length) {
            queueFiles(input.files);
        }
    });

    ['dragenter', 'dragover'].forEach(function (name) {
        dropzone.addEventListener(name, function (event) {
            event.preventDefault();
            dropzone.classList.add('border-primary');
        });
    });

    ['dragleave', 'drop'].forEach(function (name) {
        dropzone.addEventListener(name, function (event) {
            event.preventDefault();
            dropzone.classList.remove('border-primary');
        });
    });

    dropzone.addEventListener('drop', function (event) {
        if (event.dataTransfer && event.dataTransfer.files.length) {
            queueFiles(event.dataTransfer.files);
        }
    });

});
