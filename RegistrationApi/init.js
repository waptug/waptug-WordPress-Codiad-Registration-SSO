/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad,
        scripts= document.getElementsByTagName('script'),
        path = scripts[scripts.length-1].src.split('?')[0],
        curpath = path.split('/').slice(0, -1).join('/')+'/';


    $(function() {    
        codiad.regisTration.init();
    });

    codiad.regisTration = {
        
        path: curpath,

        init: function() {

        },

        open: function() {
            
            codiad.modal.load(400, this.path+'dialog.php');

        },

    };

})(this, jQuery);
