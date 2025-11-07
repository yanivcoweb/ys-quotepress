<section class="ys-quote-form-wrap">

<div class="loader-circle-wrap" id="loader-circle-wrap" >
  <div class="loader" aria-label="Loading…" ></div>
</div>
<?php /*
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>

let YSQP_SIG;

function initSignaturePad() {
  const canvas = document.getElementById('ysqp-signature');
  if (!canvas) return;

  // 1) קבע גודל תצוגה (ב-CSS או כאן ב-style)
  const displayWidth  = 280; // הרוחב שרוצים על המסך
  const displayHeight = 100; // הגובה שרוצים על המסך
  canvas.style.width  = displayWidth + 'px';
  canvas.style.height = displayHeight + 'px';

  // 2) כייל את ה-canvas הפנימי לפי DPR
  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    // קבע את רזולוציית ה-canvas הפנימי
    canvas.width  = Math.round(displayWidth  * ratio);
    canvas.height = Math.round(displayHeight * ratio);

    const ctx = canvas.getContext('2d');
    // איפוס טרנספורמציה ואז סקייל
    ctx.setTransform(1,0,0,1,0,0);
    ctx.scale(ratio, ratio);

    // אם כבר קיימת חתימה — נקה כי הקואורדינטות השתנו
    if (YSQP_SIG) YSQP_SIG.clear();
  }

  resizeCanvas();
  window.addEventListener('resize', resizeCanvas, { passive: true });

  // 3) עכשיו אתחל את SignaturePad
  YSQP_SIG = new SignaturePad(canvas, {
    minWidth: 0.8,
    maxWidth: 2.2
  });
  
    // נקי: ודא שזה לא שולח טופס
  const clearBtn = document.getElementById('ysqp-sig-clear');
  if (clearBtn) {
    clearBtn.addEventListener('click', (e) => {
      e.preventDefault(); // ליתר בטחון
	console.log('ysqp-sig-clear click');
      if (YSQP_SIG) YSQP_SIG.clear();
    });
  }
  
}

document.addEventListener('DOMContentLoaded', initSignaturePad);


</script>
*/ ?>
<style>
.signature-wrap {
	position: relative;
}
.sig-pad {
  border: 1px solid #ddd; 
  border-radius: 6px; 
  width: 280px; 
}
#ysqp-signature {
  display: block;
  touch-action: none;
  user-select: none;
}

.sig-actions {
	position: absolute;
	z-index: 1;
	bottom: 2px;
	right: 2px;
}

button#ysqp-sig-clear {
    width: 25px;
    height: 25px;
    padding: 2px;
    border: 0;
    border-radius: 6px;
    background: #fff;
    color: #fff;
    cursor: pointer;
}
button#ysqp-sig-clear:hover{  }
button#ysqp-sig-clear:focus-visible { outline:2px solid #2684ff; outline-offset:2px; }
button#ysqp-sig-clear svg {
    width: 100%;
    height: auto;
}
</style>

<form class="ys-quote-form form-validation-v3" method="post" action="#">

	<div style="display:none;">
		<input type="hidden" name="utm_campaign" value="">
		<input type="hidden" name="utm_source" value="">
		<input type="hidden" name="utm_medium" value="">
		<input type="hidden" name="utm_term" value="">
		<input type="hidden" name="utm_content" value="">
		<input type="hidden" name="form_post_id" value="">
		<input type="hidden" name="param" value="">
		<input type="hidden" name="site_title" value="Website - Site Title">
		<input type="hidden" name="form_name" value="Quote Form">
		<input type="hidden" name="page_url" value="">
		<input type="hidden" name="mail_to" value="">
		<input type="hidden" name="quote_id" value="<?php echo esc_attr(get_the_ID()); ?>">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url('/quote-thank-you/')); ?>">
		<?php /* אם לוקליזציה ל-JS בשימוש, אפשר לוותר */ ?>
		<input type="hidden" name="_ysqp" value="<?php echo esc_attr( wp_create_nonce('ysqp_form') ); ?>">
	</div>

  <div class="from-row">
  
    <div class="from-column">
      <div class="form__field_wrap">
		<label>שם פרטי</label>
        <p class="form__field">
          <input aria-required="true" maxlength="40" name="first_name" data-required="required" type="text" placeholder="*מלא/י את השם פרטי">
        </p>
        <div class="error-message field-is-required" data-inputname="first_name">שדה חובה</div>
      </div>
    </div>
	
    <div class="from-column">
      <div class="form__field_wrap">
		<label>שם משפחה</label>
        <p class="form__field">
          <input aria-required="true" maxlength="80" name="last_name" data-required="required" type="text" placeholder="*מלא/י את השם משפחה">
        </p>
        <div class="error-message field-is-required" data-inputname="last_name">שדה חובה</div>
      </div>
    </div>
	
    <div class="from-column">
      <div class="form__field_wrap">
		<label>טלפון</label>
        <p class="form__field">
          <input aria-required="true" maxlength="40" name="phone" data-required="required" type="text" placeholder="*מלא/י את מספר הטלפון">
        </p>
        <div class="error-message field-is-required" data-inputname="phone">שדה חובה</div>
      </div>
    </div>	
	
	</div>	
	<div class="signature-wrap">
	  <label>חתימה</label>
	  <div class="sig-pad">
		<canvas id="ysqp-signature" width="280" height="100"></canvas>
	  </div>
	  <div class="sig-actions" >
		<button type="button" id="ysqp-sig-clear" class="button">  
		<svg fill="#000000" height="800px" width="800px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
			 viewBox="0 0 511.998 511.998" xml:space="preserve">
		<g>
			<g>
				<path d="M442.471,76.017h-65.737V52.262C376.734,23.445,353.289,0,324.472,0H187.528c-28.818,0-52.262,23.445-52.262,52.262
					v23.754H69.529c-24.316,0-44.097,19.781-44.097,44.097v31.984c0,24.316,19.781,44.097,44.097,44.097h3.052l41.601,301.329
					c1.146,8.296,8.237,14.475,16.612,14.475h250.408c8.375,0,15.467-6.179,16.612-14.475l41.601-301.329h3.053
					c24.316,0,44.097-19.781,44.097-44.097v-31.984C486.568,95.798,466.786,76.017,442.471,76.017z M168.805,52.262
					c0-10.324,8.399-18.724,18.724-18.724h136.943c10.324,0,18.724,8.399,18.724,18.724v23.754h-174.39V52.262z M366.591,478.461
					H145.408l-38.97-282.266h299.123L366.591,478.461z M453.029,152.098c0,5.822-4.736,10.558-10.558,10.558
					c-12.961,0-359.208,0-372.942,0c-5.822,0-10.558-4.736-10.558-10.558v-31.985c0-5.822,4.736-10.558,10.558-10.558
					c5.472,0,359.996,0,372.942,0c5.822,0,10.558,4.736,10.558,10.558V152.098z"/>
			</g>
		</g>
		<g>
			<g>
				<path d="M255.137,230.524c-9.261,0-16.769,7.508-16.769,16.769v172.243c0,9.261,7.508,16.769,16.769,16.769
					c9.261,0,16.769-7.508,16.769-16.769V247.293C271.906,238.032,264.398,230.524,255.137,230.524z"/>
			</g>
		</g>
		<g>
			<g>
				<path d="M209.266,417.146l-24.803-172.243c-1.32-9.167-9.813-15.534-18.987-14.208c-9.167,1.32-15.528,9.821-14.208,18.987
					l24.803,172.243c1.321,9.176,9.831,15.528,18.987,14.208C204.225,434.813,210.586,426.312,209.266,417.146z"/>
			</g>
		</g>
		<g>
			<g>
				<path d="M346.524,230.696c-9.152-1.32-17.668,5.042-18.987,14.208l-24.804,172.241c-1.319,9.166,5.041,17.668,14.208,18.987
					c9.157,1.32,17.667-5.035,18.987-14.208l24.804-172.241C362.053,240.517,355.692,232.015,346.524,230.696z"/>
			</g>
		</g>
		</svg>
		</button>
	  </div>
	</div>
 	<?php /*
	<div class="from-row">
 
     <div class="from-column">
      <div class="form__field_wrap">
        <p class="form__field">
          <input aria-required="true" maxlength="40" name="email" data-required="required" type="text" data-fieldtype="email" placeholder="אימייל">
        </p>
        <div class="error-message field-is-required" data-inputname="email" data-fieldtype="email">
          <span class="email-empty">שדה חובה</span>
          <span class="email-incurrected">כתובת אימייל לא תקינה</span>
        </div>
      </div>
    </div> 
  
    <div class="from-column">
      <div class="form__field_wrap">
        <p class="form__field">
          <input aria-required="true" maxlength="40" name="company" data-required="required" type="text" placeholder="שם החברה">
        </p>
        <div class="error-message field-is-required" data-inputname="company">שדה חובה</div>
      </div>
    </div>
	
    <div class="from-column">
      <div class="form__field_wrap">
        <p class="form__field">
          <input aria-required="true" maxlength="40" name="company_id" data-required="required" type="text" placeholder="ח.פ">
        </p>
        <div class="error-message field-is-required" data-inputname="company_id">שדה חובה</div>
      </div>
    </div>
	
  </div>

    <div class="from-column">
      <div class="form__field_wrap">
        <p class="form__field">
          <select aria-required="true" class="form__icon" name="m7v3x7tm" data-required="required">
            <option disabled selected value="">מדינה</option>
            <option value="Israel">Israel</option>
            <option value="United States">United States</option>
            <option value="Spain">Spain</option>
            <option value="Germany">Germany</option>
          </select>
        </p>
        <div class="error-message field-is-required" data-inputname="m7v3x7tm">שדה חובה</div>
      </div>
    </div>

  <div class="form__field_wrap">
    <p class="form__field">
      <select aria-required="true" class="form__icon" name="mch2IJnf" data-required="required">
        <option disabled selected value="">תחום פעילות</option>
        <option value="Manufacturing">Manufacturing</option>
        <option value="Healthcare">Healthcare</option>
        <option value="Retail">Retail</option>
      </select>
    </p>
    <div class="error-message field-is-required" data-inputname="mch2IJnf">שדה חובה</div>
  </div>

  <div class="form__field_wrap">
    <div class="form__field">
      <textarea cols="30" name="g6zk67ne" rows="6" placeholder="הודעה (לא חובה)"></textarea>
    </div>
  </div>

  <div class="form__footer flow">
    <div class="from__field from__field_checked">
      <input checked="true" name="e89pemc3" type="checkbox" value="1" data-required="required">
      <label><small class="checkbox-text">אני מסכים לתנאים</small></label>
    </div>
    <div class="error-message field-is-required" data-inputname="e89pemc3">שדה חובה</div>
  </div>
	*/ ?>
  <!-- reCAPTCHA (אופציונלי) -->
  <!-- אם תרצה להשתמש: בטל הערה לשלושת הבלוקים למטה -->
  <!--
		  {% if block.block_eleven_use_google_recaptcha %}
		  {% if google_recaptcha_key_site and google_recaptcha_key_secret %}
		  <script src="https://www.google.com/recaptcha/api.js"></script>
		  <div>
			  <div name="recaptcha" class="g-recaptcha" data-sitekey="{{ google_recaptcha_key_site }}" data-callback="captchaVerifyV3"></div>
			  <div class="error-message  field-is-required" data-inputname="recaptcha" data-fieldtype="recaptcha">
				<p>Please let us know you are not a robot (or a spammer).</p>
			  </div>
		  </div>
		  {% endif %}
		  {% endif %}
  -->

  <div class="display-flex ys-btn-submit">
  <p>
    <button class="ys-btn ys-btn--ghost" type="submit">שלח</button>
  </p>
  </div>
</form>

<div class="form-validation-ok">
	<div><p>הטופס תקין. (אין שליחה בשלב זה)</p></div>
	<div>
		<a class="ys-download-pdf-btn ys-btn--ghost" href="" download>
			<?php esc_html_e('הורדת PDF', 'ys-quotepress'); ?>
		</a>&nbsp;&nbsp;
		<a class="ys-show-pdf-btn ys-btn--ghost" href="" target="_blank">
			<?php esc_html_e('צפה בPDF', 'ys-quotepress'); ?>
		</a>
	</div>
</div>

</section>
