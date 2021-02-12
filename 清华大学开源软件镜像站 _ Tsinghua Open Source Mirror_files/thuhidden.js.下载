'use strict';

if (!String.prototype.endsWith) {
  String.prototype.endsWith = function (ending) {
    if (typeof ending !== 'string') return false;
    if (!ending) return true;
    return this.slice(-ending.length) === ending;
  };
}
if (!document.location.hostname.endsWith('tsinghua.edu.cn') && !document.location.hostname.endsWith('tsinghua.edu.cn.')) {
  document.title = document.title.replace(/(清华)|(tsinghua)|(tuna)/gi, '');
  $().ready(function () {
    $(document.body).addClass('nonthu');
  });
}