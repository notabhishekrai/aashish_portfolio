(function () {
  // Repeating field groups (stats, services, socials, nav links, credits,
  // tags, paragraphs, stills, ...). Each `[data-repeater]` container holds
  // `.repeater-items` (existing rows), a `<template>` with __INDEX__ tokens
  // in its input names, and a `.repeater-add` button. New rows get the next
  // free numeric index substituted in, so PHP receives a real indexed array
  // ($_POST['stats'][0], [1], ...) instead of relying on bare `[]`, which
  // does not reliably group multiple fields into the same row.
  document.querySelectorAll('[data-repeater]').forEach(function (repeater) {
    var container = repeater.querySelector('.repeater-items');
    var template = repeater.querySelector('template');
    var addBtn = repeater.querySelector('.repeater-add');
    if (!container) return;

    var nextIndex = container.querySelectorAll('.repeater-item').length;

    function bindRemove(item) {
      var btn = item.querySelector('.repeater-remove');
      if (btn) {
        btn.addEventListener('click', function () {
          item.remove();
        });
      }
    }

    container.querySelectorAll('.repeater-item').forEach(bindRemove);

    if (addBtn && template) {
      addBtn.addEventListener('click', function () {
        var html = template.innerHTML.split('__INDEX__').join(String(nextIndex));
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        var item = wrapper.firstElementChild;
        if (!item) return;
        container.appendChild(item);
        bindRemove(item);
        nextIndex++;
      });
    }
  });

  // Confirm before any destructive POST (delete buttons render a plain
  // form with this class rather than a bare link, so it works with JS off
  // too -- this just adds a confirmation on top).
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.dataset.confirm)) {
        e.preventDefault();
      }
    });
  });
})();
