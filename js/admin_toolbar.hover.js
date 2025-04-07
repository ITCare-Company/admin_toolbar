/**
 * @file
 * Admin Toolbar default hover behavior for the display of the menu.
 */

((once) => {
  /**
   * Implements the Admin Toolbar default hover behavior.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the display of the menu on hover.
   */
  Drupal.behaviors.adminToolbarHover = {
    attach: (context) => {
      if (context !== document) {
        return;
      }
      // Attach Vanilla JS hover behavior to the menu items.
      once('admin-toolbar-hover', 'body', context).forEach((element) => {
        element
          .querySelectorAll(
            '.toolbar-tray.toolbar-tray-horizontal .menu-item.menu-item--expanded',
          )
          .forEach((item) => {
            /* eslint max-nested-callbacks: ["error", 5] */
            item.addEventListener('mouseenter', () => {
              item.parentElement
                .querySelector('li')
                .classList.remove('hover-intent');
              item.classList.add('hover-intent');
            });
            item.addEventListener('mouseleave', () => {
              item.classList.remove('hover-intent');
            });
          });
      });
    },
  };
})(once);
