/**
 * @file
 * Script to load the Apply Iframe to the page.
 */

(($, Drupal) => {
  'use strict';

  let loaded = false;

  let load = (id) => {
    if (!loaded && typeof Grnhse != 'undefined') {
      loaded = true;
      Grnhse.Iframe.load(id);
    }
  }

  Drupal.behaviors.gh_jobs_apply = {
    attach: function (context, settings) {
      const id = settings.GHJobs.jid;
      load(id);
    }
  };
})(jQuery, Drupal);
