(function ($) {
  function showSection(slug) {
    $('.latepoint-hierarchy-section').removeClass('is-active');
    $('.latepoint-hierarchy-section[data-section="' + slug + '"]').addClass('is-active');
    $('.latepoint-hierarchy-menu a').removeClass('is-active');
    $('.latepoint-hierarchy-menu a[data-section="' + slug + '"]').addClass('is-active');
  }

  $(document).on('click', '.latepoint-hierarchy-menu a', function (event) {
    event.preventDefault();
    const slug = $(this).data('section');
    showSection(slug);
  });

  $(document).ready(function () {
    const firstLink = $('.latepoint-hierarchy-menu a').first();
    if (firstLink.length) {
      showSection(firstLink.data('section'));
    }
  });
})(jQuery);
