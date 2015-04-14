var schuldenuhr = (function($)
{
    var _debts_national = 10000000000;
    var _debts_person   = 1000.00;
    var _debts_grow     = 100;
    var _debts_start    = 1500000000;

    var ajax_query = (function()
    {
        // Get plugin directory path:
        // https://codex.wordpress.org/Determining_Plugin_and_Content_Directories
        // http://stackoverflow.com/questions/12426710/wordpress-how-can-i-pick-plugins-directory-in-my-javascript-file
        // http://stackoverflow.com/questions/9631910/write-php-inside-javascript-alert
        // http://wordpress.org/support/topic/plugin-javascript-trying-to-reference-plugins-directory
        // http://codex.wordpress.org/Function_Reference/wp_localize_script
        var url_json = ism_schuldenuhr_url_json + "js/ism-schuldenuhr.json";

        // http://api.jquery.com/jquery.getjson/
        jQuery.getJSON( url_json , function( json ) {
            // http://stackoverflow.com/questions/19239217/json-parse-uncaught-syntaxerror-unexpected-token-o
            // var json = JSON.parse(data);

            // set new values based on json
            _debts_national = json.debt_national;
            _debts_person   = json.debt_person;
            _debts_grow     = json.debt_grow;
            _debts_start    = json.debt_start;

            // refresh
            add_debt_clock('ism-schuldenuhr-canvas');
        });

        /*jQuery.ajax({
            type: "GET",
            dataType: "json",
            url: url_json,
            data: ({ 'rnd' : Math.random() }),
            async: true,
            timeout: 40000,
            success: function(_success ) {
                // alert(_success);
                _debts_national = _success.debt_national;
                _debts_person = _success.debt_person;
                _debts_grow = _success.debt_grow;
                _debts_start = _success.start;
            }
        });*/
    });

    var format = (function(input) {
        input = '' + Math.round(input);
        if (input.length > 3) {
            var mod = input.length % 3;
            var output = (mod > 0 ? (input.substring(0, mod)) : '');
            for (var i = 0; i < Math.floor(input.length / 3); i++) {
                if ((mod == 0) && (i == 0))
                    output += input.substring(mod + 3 * i, mod + 3 * i + 3);
                else
                    output += '.' + input.substring(mod + 3 * i, mod + 3 * i + 3);
            }
            return(output);
        } else return input;
    });

    var set_digits = (function(val, id) {
        document.getElementById(id).innerHTML = format(val) + " &#8364;";
    });

    var set_clock = (function() {
        var debt_time = new Date();
        set_digits(Math.round(_debts_national + (debt_time.getTime() / 1000 - _debts_start) * _debts_grow), 'debt1');
        setTimeout('schuldenuhr.set_clock()', 1000);
    });

    var add_debt_clock = (function(id) {
        print_sheet(id);
        set_digits(_debts_grow, 'debt2');
        set_digits(_debts_person, 'debt3');
        set_clock();
    });

    var print_sheet = (function(id) {

        if ( (typeof ism_schuldenuhr_donate == 'undefined') || (ism_schuldenuhr_donate == false) ) {
            var html_link = "";
        } else {
            var html_link =
                "<tr>" +
                "<td colspan='2' style='font-size: xx-small;'>powered by <a href='https://www.coininvest.com'>www.CoinInvest.com</a></td>" +
                "</tr>";
        }

        var html_table =
            "<table>" +
            "<thead>" +
            "<tr>" +
            "<th colspan='2'>Staatsverschuldung Deutschland</th>" +
            "</tr>" +
            "</thead>" +
            "<tbody>" +
            "<tr>" +
            "<td colspan='2'><span id='debt1'>0</span></td>" +
            "</tr>" +
            "<tr>" +
            "<th>Zuwachs / Sek.</th>" +
            "<th>Schulden / Kopf</th>" +
            "</tr>" +
            "<tr>" +
            "<td><span id='debt2'>0</span></td>" +
            "<td><span id='debt3'>0</span></td>" +
            "</tr>" +
            html_link +
            "</tbody>" +
            "</table>";

        document.getElementById(id).innerHTML = html_table;
    });

    var initialize = (function() {
        jQuery(document).ready(function() {
            ajax_query(_debts_national, _debts_person, _debts_grow, _debts_start);
            add_debt_clock('ism-schuldenuhr-canvas');
        });
    });

    return {
        format         : format,
        set_digits     : set_digits,
        set_clock      : set_clock,
        add_debt_clock : add_debt_clock,
        print_sheet    : print_sheet,
        initialize     : initialize
    };

} (jQuery) );

schuldenuhr.initialize();
