define(['core/modal_confirm', 'core/modal_events'], function(ModalConfirm, ModalEvents) {

    const init = function(cancelSelector, targetId) {
        const cancelButton = document.querySelector(cancelSelector);
        if (!cancelButton) {
            return;
        }

        cancelButton.addEventListener('click', function(e) {
            e.preventDefault();

            ModalConfirm.create({
                title: 'Änderungen verwerfen?',
                question: 'Nicht gespeicherte Änderungen gehen verloren. Möchten Sie fortfahren?',
                continueButtonText: 'Ja',
                cancelButtonText: 'Nein'
            }).then(function(modal) {

                modal.getRoot().on(ModalEvents.save, function() {
                    window.location.href = M.cfg.wwwroot + '/mod/deinplugin/view.php?id=' + targetId;
                });

                modal.show();
            });
        });
    };

    return { init: init };
});
