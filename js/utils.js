/**
 * Função para detectar o nome do cliente em execução
 * @returns {String}
 */
function _dir() {
        var url = window.location.href;
        url = url.replace("http://", "");

        var urlExplode = url.split("/");
        var serverName = urlExplode[0];

        serverName = 'http://' + serverName +_CFG + "/";
        return serverName;
}

var action = _dir() + "mainframe/actions.php";
var loader = _dir() + "img/loader.gif";

// Limit scope pollution from any deprecated API
(function() {

        var matched, browser;

// Use of jQuery.browser is frowned upon.
// More details: http://api.jquery.com/jQuery.browser
// jQuery.uaMatch maintained for back-compat
        jQuery.uaMatch = function(ua) {
                ua = ua.toLowerCase();

                var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
                        /(webkit)[ \/]([\w.]+)/.exec(ua) ||
                        /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
                        /(msie) ([\w.]+)/.exec(ua) ||
                        ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
                        [];

                return {
                        browser: match[ 1 ] || "",
                        version: match[ 2 ] || "0"
                };
        };

        matched = jQuery.uaMatch(navigator.userAgent);
        browser = {};

        if (matched.browser) {
                browser[ matched.browser ] = true;
                browser.version = matched.version;
        }

// Chrome is Webkit, but Webkit is also Safari.
        if (browser.chrome) {
                browser.webkit = true;
        } else if (browser.webkit) {
                browser.safari = true;
        }

        jQuery.browser = browser;

        jQuery.sub = function() {
                function jQuerySub(selector, context) {
                        return new jQuerySub.fn.init(selector, context);
                }
                jQuery.extend(true, jQuerySub, this);
                jQuerySub.superclass = this;
                jQuerySub.fn = jQuerySub.prototype = this();
                jQuerySub.fn.constructor = jQuerySub;
                jQuerySub.sub = this.sub;
                jQuerySub.fn.init = function init(selector, context) {
                        if (context && context instanceof jQuery && !(context instanceof jQuerySub)) {
                                context = jQuerySub(context);
                        }

                        return jQuery.fn.init.call(this, selector, context, rootjQuerySub);
                };
                jQuerySub.fn.init.prototype = jQuerySub.fn;
                var rootjQuerySub = jQuerySub(document);
                return jQuerySub;
        };

})();

/**
 * Função que detecta e retorna o nome do browser, se retorno = view
 * retorna a versao do navegador, se nao retorna o nome
 * @param {String} retorno
 * @returns {String|detectBrowserVersion.userAgent}
 */
function detectBrowserVersion(retorno) {
        var userAgent = navigator.userAgent.toLowerCase();
        $.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
        var version = 0;
        nav = '';

        // Is this a version of IE?
        if ($.browser.msie) {
                userAgent = $.browser.version;
                userAgent = userAgent.substring(0, userAgent.indexOf('.'));
                version = userAgent;
                nav = 'msie';
        }

        // Is this a version of Chrome?
        if ($.browser.chrome) {
                userAgent = userAgent.substring(userAgent.indexOf('chrome/') + 7);
                userAgent = userAgent.substring(0, userAgent.indexOf('.'));
                version = userAgent;
                // If it is chrome then jQuery thinks it's safari so we have to tell it it isn't
                $.browser.safari = false;
                nav = 'chrome';
        }

        // Is this a version of Safari?
        if ($.browser.safari) {
                userAgent = userAgent.substring(userAgent.indexOf('safari/') + 7);
                userAgent = userAgent.substring(0, userAgent.indexOf('.'));
                version = userAgent;
                nav = 'safari';
        }

        // Is this a version of Mozilla?
        if ($.browser.mozilla) {
                //Is it Firefox?
                if (navigator.userAgent.toLowerCase().indexOf('firefox') != -1) {
                        userAgent = userAgent.substring(userAgent.indexOf('firefox/') + 8);
                        userAgent = userAgent.substring(0, userAgent.indexOf('.'));
                        version = userAgent;
                        nav = 'mozilla';
                }
                // If not then it must be another Mozilla
                else {
                }
        }

        // Is this a version of Opera?
        if ($.browser.opera) {
                userAgent = userAgent.substring(userAgent.indexOf('version/') + 8);
                userAgent = userAgent.substring(0, userAgent.indexOf('.'));
                version = userAgent;
                nav = 'opera';
        }

        if (retorno == 'ver')
                return version;
        else
                return nav;
}

/**
 * Testa se o browser é IE, se for exibe em .content uma mensagem de erro
 * @returns {undefined}
 */
function _browserCompativel() {
        if (detectBrowserVersion() == 'msie') {
                $(".content").addClass("ui-state-error ui-corner-all").css({
                        "height": "200px",
                        "width": "460px",
                        "padding": "10px",
                        "margin-top": "50px"
                });
                $(".content").html("<p style='text-align: center; font-size: 18px;'><b>Desculpe, mas este navegador não é suportado neste sistema, por não trabalhar com as últimas tecnologias.</b><br /><br />Utilize os navegadores indicados neste endereço.<br /><a href='http://www.cnecvirtual.com.br/downloads.php' style='color: blue;'>Área de Downloads</a></p><br /><table style='margin:0 auto; width:100%;'><td align='center'><a href='http://br.mozdev.org/'><img src='http://br.mozdev.org/firefox-logo.png' style='border: 0px solid; width: 100px; height: 26px;' alt='Botão' /></a><a href='http://br.mozdev.org/'></a></td><td align='center'><a href='https://www.google.com/chrome?hl=pt-br'><img src='https://www.google.com/intl/pt-BR/chrome/assets/common/images/chrome_logo_2x.png' style='border: 0px solid; width: 100px; height: 26px;' alt='Botão' height='67' width='94' /></a><a href='https://www.google.com/chrome?hl=pt-br'></a></td><td align='center'><a href='https://www.google.com/chrome?hl=pt-br'></a></td></table>");
        }
        ;
}

function verificaEmail(sEmail) {
        // filtros
        var emailFilter = /^.+@.+\..{2,}$/;
        var illegalChars = /[\(\)\<\>\,\;\:\\\/\"\[\]]/;
        // condição
        if (!(emailFilter.test(sEmail)) || sEmail.match(illegalChars)) {
                return false;
        } else {
                return true;
        }
}

function shorTable(tbl) {
        $(tbl).dataTable({
                "bPaginate": true,
                "bLengthChange": true,
                "bFilter": false,
                "bSort": true,
                "bInfo": false,
                "bAutoWidth": true,
                "bJQueryUI": false,
                "aLengthMenu": [
                        [10, 100, 200, 300, -1],
                        [10, 100, 200, 300, "Todos"]
                ],
                "oLanguage": {
                        "sUrl": "mainframe/plugins/jquery/DataTables-1.9.4/datatables.Portuguese.txt"
                }
        });
}      

function loadForum(){
        $("#selectForum").load(action, { "action" : "getForumCursos", "curso" : $("#selCurso :selected").val()}, function(){

        })
}