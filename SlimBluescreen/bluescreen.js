/**
 * Debugger Bluescreen
 *
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

(function() {
    var bs = document.getElementById('netteBluescreen');
    document.body.appendChild(bs);
    document.onkeyup = function(e) {
        e = e || window.event;
        if (e.keyCode == 27 && !e.shiftKey && !e.altKey && !e.ctrlKey && !e.metaKey) {
            document.getElementById('netteBluescreenIcon').click();
        }
    };

    for (var i = 0, styles = document.styleSheets; i < styles.length; i++) {
        if ((styles[i].owningElement || styles[i].ownerNode).className !== 'nette-debug') {
            styles[i].oldDisabled = styles[i].disabled;
            styles[i].disabled = true;

        } else if (styles[i].addRule) {
            styles[i].addRule('.nette-collapsed', 'display: none');
        } else {
            styles[i].insertRule('.nette-collapsed { display: none }', 0);
        }
    }

    bs.onclick = function(e) {
        e = e || window.event;
        if (e.shiftKey || e.altKey || e.ctrlKey || e.metaKey) {
            return;
        }

        for (var link = e.target || e.srcElement; link && (!link.tagName || link.className.indexOf('nette-toggle') < 0); link = link.parentNode) {}
        if (!link) {
            return true;
        }

        var collapsed = link.className.indexOf('nette-toggle-collapsed') > -1,
            ref = link.getAttribute('data-ref') || link.getAttribute('href', 2),
            dest;

        if (ref && ref !== '#') {
            dest = document.getElementById(ref.substring(1));
        } else {
            for (dest = link.nextSibling; dest && dest.nodeType !== 1; dest = dest.nextSibling) {}
        }

        link.className = 'nette-toggle' + (collapsed ? '' : '-collapsed');
        dest.style.display = collapsed ? (dest.tagName.toLowerCase() === 'div' ? 'block' : 'inline') : 'none';

        if (link.id === 'netteBluescreenIcon') {
            for (var i = 0, styles = document.styleSheets; i < styles.length; i++) {
                if ((styles[i].owningElement || styles[i].ownerNode).className !== 'nette-debug') {
                    styles[i].disabled = collapsed ? true : styles[i].oldDisabled;
                }
            }
        }
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
    };
})();