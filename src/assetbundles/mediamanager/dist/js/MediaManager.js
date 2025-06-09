/**
 * Media Manager
 *
 * @package       PaperTiger:MediaManager
 * @author        Paper Tiger
 * @copyright     Copyright (c) 2020 Paper Tiger
 * @link          https://www.papertiger.com/
 */
(function ($) {
  /** global: Craft */
  /** global: Garnish */
  Craft.SyncScheduler = Garnish.Base.extend(
    {
      init: function () {
        var self = this;

        $('.delete').on('click', function (e) {
          self.deleteSync(e.target.dataset.id);
        });
      },

      deleteSync: function (id) {
        var data = {
          scheduledSyncId: id,
          isJson: true,
        };
        Craft.sendActionRequest('POST', 'mediamanager/scheduled-sync/delete', {data})
          .then((response) => {
            if (response.data.success) {
              setTimeout(function () {
                location.href = Craft.getUrl('mediamanager/scheduler/');
              }, 1000);
            }
            else if (response.data.errors) {
              var errors = this.flattenErrors(response.data.errors);
              Craft.cp.displayError(Craft.t('mediamanager', 'Could not start cleaning:') + "\n\n" + errors.join("\n"));
            }
          })
          .catch(({ response }) => {
            Craft.cp.displayError();
          })
      },

    }
  );
})(jQuery);
