(function (Drupal, once, drupalSettings) {
  Drupal.behaviors.staffContactModal = {
    attach(context) {
      const settings = drupalSettings || {};
      const contactDialog = context.querySelector('#staff-contact-modal');
      if (!contactDialog) {
        return;
      }

      const statusDialog = document.getElementById('staff-contact-status-modal');
      const contactForm = document.getElementById('staff-contact-form');
      const tidInput = document.getElementById('staff-contact-tid');
      const modalTitle = document.getElementById('staff-contact-modal-title');
      const statusMessage = document.getElementById('staff-contact-status-message');
      const continueButton = document.getElementById('staff-contact-continue');
      const submitButton = document.getElementById('staff-contact-send');
      const headingPrefix = settings.montreal2027Tools &&
        typeof settings.montreal2027Tools.staffContactHeadingPrefix === 'string' &&
        settings.montreal2027Tools.staffContactHeadingPrefix !== ''
        ? settings.montreal2027Tools.staffContactHeadingPrefix
        : 'Send a message to the ';

      if (!statusDialog || !contactForm || !tidInput || !modalTitle || !statusMessage || !continueButton || !submitButton) {
        return;
      }

      const getDivisionHtml = (trigger) => {
        const row = trigger.closest('.staff-row');
        if (!row) {
          return '';
        }

        let sibling = row.previousElementSibling;
        while (sibling) {
          if (sibling.classList && sibling.classList.contains('division')) {
            return sibling.innerHTML;
          }
          sibling = sibling.previousElementSibling;
        }

        return '';
      };

      const updateScrollLock = () => {
        const anyOpen = Boolean(document.querySelector('dialog[open]'));
        document.body.classList.toggle('staff-modal-open', anyOpen);
      };

      const openDialog = (dialog) => {
        if (!dialog.open) {
          dialog.showModal();
        }
        updateScrollLock();
      };

      const closeDialog = (dialog) => {
        if (dialog.open) {
          dialog.close();
        }
        updateScrollLock();
      };

      const closeAllDialogs = () => {
        closeDialog(contactDialog);
        closeDialog(statusDialog);
        document.body.classList.remove('staff-modal-open');
      };

      once('staff-contact-trigger', '.contact-link[value]', context).forEach((trigger) => {
        trigger.addEventListener('click', () => {
          const divisionHtml = getDivisionHtml(trigger);
          modalTitle.innerHTML = divisionHtml !== ''
            ? headingPrefix + divisionHtml
            : headingPrefix.trim();

          contactForm.reset();
          statusMessage.textContent = '';
          tidInput.value = trigger.value;
          openDialog(contactDialog);
          const nameInput = document.getElementById('staff-contact-name');
          if (nameInput) {
            nameInput.focus();
          }
        });
      });

      [contactDialog, statusDialog].forEach((dialog) => {
        dialog.addEventListener('close', updateScrollLock);
        dialog.addEventListener('click', (event) => {
          if (event.target === dialog) {
            closeDialog(dialog);
          }
        });
      });

      continueButton.addEventListener('click', closeAllDialogs);

      contactForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!contactForm.reportValidity()) {
          return;
        }

        submitButton.disabled = true;

        const fallbackMessage = 'There was a problem sending your message.  Please try again later.';
        let messageToShow = fallbackMessage;

        try {
          const response = await fetch(contactForm.action, {
            method: 'POST',
            body: new FormData(contactForm),
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          });

          const payload = await response.json().catch(() => ({}));

          if (response.ok && payload.success) {
            messageToShow = payload.message || messageToShow;
          }
          else {
            messageToShow = payload.message || messageToShow;
          }
        }
        catch (error) {
          messageToShow = fallbackMessage;
        }
        finally {
          submitButton.disabled = false;
        }

        statusMessage.textContent = messageToShow;
        closeDialog(contactDialog);
        openDialog(statusDialog);
      });
    }
  };
})(Drupal, once, window.drupalSettings || {});
