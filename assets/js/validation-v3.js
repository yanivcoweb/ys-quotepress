console.log('validation-v3.js 8.1');

// גלובלי
window.YSQP_SIG = null;

window.ysqpInitSignaturePad = function ysqpInitSignaturePad() {
  const canvas = document.getElementById('ysqp-signature');
  if (!canvas) {
    console.warn('signature canvas not found');
    return;
  }

  // קבע מימדי תצוגה קבועים (או קח מ-CSS) וסקל ל-DPR
  const displayW = 280, displayH = 100;
  canvas.style.width = displayW + 'px';
  canvas.style.height = displayH + 'px';

  const ratio = Math.max(window.devicePixelRatio || 1, 1);
  canvas.width  = Math.round(displayW * ratio);
  canvas.height = Math.round(displayH * ratio);
  const ctx = canvas.getContext('2d');
  ctx.setTransform(1,0,0,1,0,0);
  ctx.scale(ratio, ratio);

  // אתחול SignaturePad
  window.YSQP_SIG = new SignaturePad(canvas, { minWidth: 0.8, maxWidth: 2.2 });
  console.log('SignaturePad init OK');

  // הוסף event listener לכפתור ניקוי
  const clearBtn = document.getElementById('ysqp-sig-clear');
  if (clearBtn) {
    clearBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (window.YSQP_SIG) {
        window.YSQP_SIG.clear();
        console.log('Signature cleared');
      }
    });
  }
};

// DOM מוכן
document.addEventListener('DOMContentLoaded', () => {
  // נסה לאתחל עכשיו
  ysqpInitSignaturePad();

  // אם זה נטען דינמית/שורטקוד, תריץ שוב אחרי טיק קצר
  setTimeout(() => {
    if (!window.YSQP_SIG) ysqpInitSignaturePad();
  }, 50);
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-validation-v3').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
			console.log('submit 1');
			if (form._submitting) return;
            event.preventDefault();

            // const loader = form.querySelector('.loader-circle-wrap');
            // if (loader) loader.style.display = 'flex';
			document.getElementById('loader-circle-wrap').style.display = 'flex';
			

            let isFormValid = validateFormFields(form);
			console.log('isFormValid',isFormValid);
            if (!isFormValid) {
				document.getElementById('loader-circle-wrap').style.display = 'none';
				
                return false;
            }
			
            let recaptchaPassed = await verifyRecaptchaV3(form);
			console.log('recaptchaPassed',recaptchaPassed);
            if (!recaptchaPassed) {
				document.getElementById('loader-circle-wrap').style.display = 'none';
                return false;
            }
       
			let formData = new FormData(form);
			formData.append('action', 'submit_form_entry');

			let addedSig = false;

			try {
			  if (window.YSQP_SIG) {
				console.log('sig exists. isEmpty=', YSQP_SIG.isEmpty());
				// גם אם ריק – נשלח אינדיקציה כדי לדעת בצד שרת
				const dataURL = YSQP_SIG.isEmpty() ? '' : YSQP_SIG.toDataURL('image/png');
				if (dataURL) {
				  console.log('signature dataURL length:', dataURL.length);
				  formData.append('signature_dataurl', dataURL);
				  addedSig = true;
				} else {
				  formData.append('signature_empty', '1');
				  console.log('signature empty (no drawing)');
				}
			  } else {
				console.warn('YSQP_SIG is undefined (init not ran / wrong id)');
			  }
			} catch (e) {
			  console.error('signature block error', e);
			}

			// דיבוג לראות מה נשלח בפועל
			console.log('FORMDATA KEYS:', Array.from(formData.keys()));

			// הוסף nonce אם לוקליזציה בשימוש
			if (typeof YSQP !== 'undefined' && YSQP.nonce) {
			  formData.append('_ysqp', YSQP.nonce);
			}

			try {
			  let response = await fetch((typeof YSQP !== 'undefined' ? YSQP.ajax_url : '/wp-admin/admin-ajax.php'), {
				method: 'POST',
				body: formData,
			  });
			  let text = await response.text();
			  console.log('RAW RESPONSE:', text);
			  let data = JSON.parse(text);

			  if (
				(data.success && data.data === 'success1') ||
				(data.success && data.data && data.data.message === 'success1')
			  ) {
				  // מציג הודעת הצלחה
				  const okDiv = document.querySelector('.form-validation-ok');
				  if (okDiv) {
					okDiv.style.display = 'block'; // מציג את האלמנט
				  }

				  const ysBtn = document.querySelector('.ys-btn');
				  if (ysBtn) {
					ysBtn.style.display = 'none'; // מציג את האלמנט
				  }

				  const downloadPdfBtn = document.querySelector('.ys-download-pdf-btn');
				  if (downloadPdfBtn && data.data.pdf_url) {
					downloadPdfBtn.href = data.data.pdf_url;
				  }
				  const showPdfBtn = document.querySelector('.ys-show-pdf-btn');
				  if (showPdfBtn && data.data.pdf_url) {
					showPdfBtn.href = data.data.pdf_url;
				  }
				  // הסתרת הטופס עצמו (לא חובה)
				  // form.style.display = 'none';
				
				return;
			  } else {
				alert('There was an error submitting the form.');
			  }
			} catch (err) {
			  console.log('error', err);
			  alert('Submission failed. Please try again later.');
			} finally {
			  document.getElementById('loader-circle-wrap').style.display = 'none';
			}

			
		});

        // Hide error message on input/change for all data-required fields
        form.querySelectorAll('[data-required]').forEach(function (input) {
            input.addEventListener('input', function () {
                let err = form.querySelector('.error-message[data-inputname="' + input.name + '"]');
                if (err) err.style.display = 'none';
            });
            if (input.tagName.toLowerCase() === 'select') {
                input.addEventListener('change', function () {
                    let err = form.querySelector('.error-message[data-inputname="' + input.name + '"]');
                    if (err) err.style.display = 'none';
                });
            }
        });
    });
});

function validateFormFields(form) {
    let isValid = true;

    // Hide all errors first
    form.querySelectorAll('.error-message').forEach(function (el) {
        el.style.display = 'none';
        // Hide specific spans for email
        let empty = el.querySelector('.email-empty');
        let inc = el.querySelector('.email-incurrected');
        if (empty) empty.style.display = 'none';
        if (inc) inc.style.display = 'none';
    });

    form.querySelectorAll('[data-required]').forEach(function (input) {
        // Email validation via data-fieldtype
        if (input.getAttribute('data-fieldtype') === 'email') {
            const emailVal = input.value.trim();
            if (!emailVal) {
                isValid = false;
                showEmailError(form, input, 'empty');
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                isValid = false;
                showEmailError(form, input, 'incorrect');
            }
        }
        // For select
        else if (input.tagName.toLowerCase() === 'select' && !input.value) {
            isValid = false;
            showError(form, input);
        }
        // For textarea
        else if (input.tagName.toLowerCase() === 'textarea' && !input.value.trim()) {
            isValid = false;
            showError(form, input);
        }
        // For checkbox (if data-required)
        else if (input.type === 'checkbox' && !input.checked) {
            isValid = false;
            showError(form, input);
        }
        // For all other fields (text, etc.)
        else if (
            input.getAttribute('data-fieldtype') !== 'email' &&
            input.tagName.toLowerCase() !== 'select' &&
            input.tagName.toLowerCase() !== 'textarea' &&
            input.type !== 'checkbox' &&
            !input.value.trim()
        ) {
            isValid = false;
            showError(form, input);
        }
    });

    return isValid;
}

function showEmailError(form, input, type) {
    let err = form.querySelector('.error-message[data-inputname="' + input.name + '"]');
    if (err) {
        let empty = err.querySelector('.email-empty');
        let inc = err.querySelector('.email-incurrected');
        if (type === 'empty') {
            if (empty) empty.style.display = 'inline';
            if (inc) inc.style.display = 'none';
        } else if (type === 'incorrect') {
            if (empty) empty.style.display = 'none';
            if (inc) inc.style.display = 'inline';
        }
        err.style.display = 'block';
    }
}

function showError(form, input) {
    let err = form.querySelector('.error-message[data-inputname="' + input.name + '"]');
    if (err) {
        err.style.display = 'block';
    }
}

function captchaVerifyV3(){
	console.log('captchaVerifyV3');
	// Try to find the recaptcha widget that was solved
    // This assumes that the currently focused element is inside the form with the solved recaptcha.
    setTimeout(function() {
		console.log('captchaVerifyV3 setTimeout');
        var active = document.activeElement;
        var form = active.closest && active.closest('form');
		console.log('captchaVerifyV3 setTimeout form',form);
        if (form) {
            let msg = form.querySelector('.error-message[data-inputname="recaptcha"]');
            if (msg) msg.style.display = 'none';
        } else {
            document.querySelectorAll('.error-message[data-inputname="recaptcha"]').forEach(function(msg) {
                msg.style.display = 'none';
            });
        }
    }, 10);
}



// Recaptcha per-form (works if g-recaptcha is inside form)
// Helper function: Get reCAPTCHA response for the given form (by widget index)
function getRecaptchaResponseForForm(form) {
	console.log('getRecaptchaResponseForForm');	
    var recaptchaDiv = form.querySelector('.g-recaptcha');
	console.log('getRecaptchaResponseForForm recaptchaDiv',recaptchaDiv);	
    if (!recaptchaDiv) return "";
    var widgets = document.querySelectorAll('.g-recaptcha');
    var widgetIndex = Array.prototype.indexOf.call(widgets, recaptchaDiv);
    return grecaptcha.getResponse(widgetIndex);
}



// Main validation function
async function verifyRecaptchaV3(form) {
    console.log('verifyRecaptchaV3');
    // Find the recaptcha widget in this form
    const recaptchaElement = form.querySelector('.g-recaptcha');
    if (!recaptchaElement) {
        console.log('reCAPTCHA widget is not present in the form.');
        return true;  // Skip reCAPTCHA validation if it's not required
    }
    // Hide previous error
    let errorMessage = form.querySelector('.error-message[data-inputname="recaptcha"]');
    if (errorMessage) errorMessage.style.display = 'none';

    // Get the response for THIS reCAPTCHA widget (by widget index)
    const response = getRecaptchaResponseForForm(form);
    if (!response) {
        console.log('reCAPTCHA validation failed. Please complete the reCAPTCHA.');
        if (errorMessage) errorMessage.style.display = 'block';
        return false;
    }

    try {
        // Optionally, validate reCAPTCHA on the server side too
        const recaptchaVerification = await fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=verify_recaptcha&response=${response}`
        });

        if (!recaptchaVerification.ok) {
            console.error(`HTTP Error: ${recaptchaVerification.status}`);
            if (errorMessage) errorMessage.style.display = 'block';
            return false;
        }

        const result = await recaptchaVerification.json();
        console.log('Parsed Response:', result);

        if (result.success) {
            console.log('reCAPTCHA validation passed.');
            return true;
        } else {
            console.log('reCAPTCHA validation failed (server-side).');
            if (errorMessage) errorMessage.style.display = 'block';
            return false;
        }
    } catch (error) {
        console.error('Error verifying reCAPTCHA:', error);
        if (errorMessage) errorMessage.style.display = 'block';
        return false;
    }
}
