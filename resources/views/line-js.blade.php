<script defer>
    class LineJs {
        constructor() {
            window.addEventListener('error', e => {
                this.send(e);
            });
            window.addEventListener('warning', e => {
                this.send(e);
            });
        }
        send(e) {
            return new Promise(function (resolve, reject) {
                console.log(e);

                let stack = e.error.stack;
                let exception = e.error.toString();

                if (stack) {
                    exception += '\n' + stack;
                }

                let data = {
                    message: e.message,
                    exception: exception,
                    file: e.filename,
                    url: window.location.origin + window.location.pathname,
                    line: e.lineno,
                    column: e.colno,
                    error: e.message,
                    stack: e.error.stack,
                };

                let xhr = new XMLHttpRequest();
                xhr.open("POST", window.location.origin + '/line-api/js-report', true);
                xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                xhr.onload = function () {
                    if (this.status >= 200 && this.status < 300) {
                        resolve(xhr.response);
                    } else {
                        reject({
                            status: this.status,
                            statusText: xhr.statusText
                        });
                    }
                };
                xhr.onerror = function () {
                    reject({
                        status: this.status,
                        statusText: xhr.statusText
                    });
                };
                xhr.send(JSON.stringify(data));
            });
        }
    }

    new LineJs();
</script>
