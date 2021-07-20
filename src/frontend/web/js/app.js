function Converter() {

    this.progress = 0;
    this.status = '';
    this.message = '';
    this.finish = false;
    this.progressBar = null;
    this.messagesConsole = null;
    this.progressBarScale = null;
    this.progressBarLabel = null;
    this.id = '';

    this.timer = {
        draw_progress: null
    };

    /**
     * Дергаем контроллер progress
     * он возвращает прогресс в процентах
     */
    this.request_progress = function() {
        var self = this;
        var d = new Date();
        var item = {
            id: this.id
        };
        $.post(
            "/progress?now="+d.getTime(),
            item,
            function(data) {
                self.progress = data.progress;
                self.status = data.status;
                self.message = data.message;
                self.render_progress();
            },
            "json"
        );
    };

    /**
     * Собственно отрисовка прогресса
     */
    this.render_progress = function() {
        this.progressBarScale.css('width', this.progress + '%');
        this.progressBarLabel.text(this.progress + '% complete');

        if (this.message.length > 0) {
            this.messagesConsole.prepend(this.message + '<hr />');
        }

        if ((this.finish == false)&&((this.status == 'success')||(this.status == 'error'))) {
            this.finish = true;
            clearInterval(this.timer.draw_progress);

            if (this.status == 'success') {
                this.progressBarLabel.removeClass("my-icon-loading");
                this.progressBarLabel.addClass("my-icon-success");
                this.messagesConsole.prepend('<br />Я не стал делать автоматический переход, потому что данная страница срабатывает очень быстро, и не успеваешь почитать логи.<br />Просто выдаю в конце ссылку.<br />Жми : <a href="/slider?id=' + this.id + '">Slider Ready - Go!</a>!<br /><br />');
                /**
                 * Вначале я сделал этот автоматический переход
                 * но страница с логами проскакивает очень быстро
                 * и не успеваешь полюбоваться
                 * поэтому он закоментирован
                 *
                 * window.location.href = '/slider?id=' + this.id;
                 */
            } else {
                this.progressBarLabel.removeClass("my-icon-loading");
                this.progressBarLabel.addClass("my-icon-error");
                this.progressBarLabel.text('Произошла ошибка');
            }
        }
    };

    /**
     * Дергаем контроллер convert
     * Если сейчас данный файл в процессе обработки -
     * т.е. мы нажали F5 - то контроллер вернет статус in_progress
     *
     * Останавливать Таймер
     * необходимо в рендере, но туда должно прийти это состояние
     * или вообще всегда останавливать в рендере.
     *
     * если мы запустили первый раз - то контроллер будет работать долго
     * пока не трансформирует файл и вернет в результате success
     */
    this.request_converter = function() {
        var d = new Date();
        var item = {
            id: this.id
        };

        $.post(
            "/convert?now="+d.getTime(),
            item,
            function(data) {},
            "json"
        );
    };

    /**
     * Инициализация
     * 1) получим доступ к контролам на странице
     * 2) запустим таймер проверки прогресса работы
     * 3) дернем контроллер convert
     */
    this.init = function() {
        var self = this;
        this.progressBar = $("#ProgressBar");
        this.messagesConsole = $("#MessagesConsole");
        this.progressBarScale = $(".progress-bar", this.progressBar);
        this.progressBarLabel = $("#ProgressLabel");

        /**
         * Вот тут мы получили идентификатор слайдера - с ним и работаем далее
         */
        this.id = this.progressBar.attr("data-id");

        /**
         * Проверяем периодически состояние прогресса
         */
        this.timer.draw_progress = setInterval(function() { self.request_progress.call(self) }, 300);

        this.request_converter();
    };

}



$.ajaxSetup({cache: false});
$(document).ready(function() {

    /**
     * Ищем на странице поле файла
     * и обрабатываем его
     */
    var n = $("#uploadform-pdffile").length;
    if (n > 0) {
        document.getElementById('uploadform-pdffile').onchange = function () {
            var filesize = document.getElementById('uploadform-pdffile').files[0].size;
            if (filesize > 50*1024*1024) {
                $("#Restrict50M")
                    .removeClass("my-icon-success")
                    .removeClass("my-icon-info")
                    .addClass("my-icon-error")
                    .text("Сработало Ограничение на размер файла более 50 Мегабайт!");
                $(".btn-primary").hide();
            } else {
                $("#Restrict50M")
                    .removeClass("my-icon-info")
                    .removeClass("my-icon-error")
                    .addClass("my-icon-success")
                    .text("Размер вашего файла: "+(filesize/1024/1024).toFixed(2)+" Мегабайт.");
                $(".btn-primary").show();
            }

        };
    }

    /**
     * Ищем на странице шкалу прогресса
     * и если найдем - то запускаем конвертацию
     * и проверку прогресса
     */
    var n = $("#ProgressBar").length;
    if (n > 0) {
        var converter = new Converter();
        converter.init();
    }
});