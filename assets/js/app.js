window.LIS = {
  toast: function(icon, title) {
    if (typeof Swal !== 'undefined') {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });
      Toast.fire({
        icon: icon || 'success',
        title: title || ''
      });
    } else {
      alert(title);
    }
  },

  alert: function(title, textOrHtml, icon) {
    if (typeof Swal !== 'undefined') {
      return Swal.fire({
        title: title || 'Pemberitahuan Sistem',
        html: textOrHtml || '',
        icon: icon || 'info',
        confirmButtonColor: '#047857',
        confirmButtonText: 'Tutup',
        customClass: {
          popup: 'rounded-2xl shadow-xl',
          confirmButton: 'lis-btn lis-btn-md lis-btn-primary'
        }
      });
    } else {
      const clean = (textOrHtml || '').replace(/<[^>]*>/g, '');
      alert((title || '') + "\n" + clean);
    }
  },

  confirm: function(title, textOrHtml, confirmBtnText) {
    if (typeof Swal !== 'undefined') {
      return Swal.fire({
        title: title || 'Apakah Anda yakin?',
        html: textOrHtml || 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#be123c',
        cancelButtonColor: '#64748b',
        confirmButtonText: confirmBtnText || 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
          popup: 'rounded-2xl shadow-xl'
        }
      });
    }
    const clean = (textOrHtml || 'Tindakan ini tidak dapat dibatalkan.').replace(/<[^>]*>/g, '');
    return Promise.resolve({ isConfirmed: confirm((title || 'Apakah Anda yakin?') + "\n" + clean) });
  },

  /**
   * Menampilkan Error Lengkap dengan Detail Teknis untuk Developer
   * Membantu pengembang dan developer mengetahui file, line number, SQL query, atau endpoint yang bermasalah.
   */
  showError: function(title, errOrResponse, defaultMsg) {
    title = title || 'Terjadi Kesalahan Sistem';
    let userMsg = defaultMsg || 'Operasi gagal diproses oleh server.';
    let techDetail = '';
    let httpStatus = '';
    let endpoint = '';

    if (typeof errOrResponse === 'string') {
      userMsg = errOrResponse;
      techDetail = errOrResponse;
    } else if (errOrResponse instanceof Error) {
      userMsg = errOrResponse.message || userMsg;
      techDetail = errOrResponse.technical || errOrResponse.stack || errOrResponse.message || '';
      httpStatus = errOrResponse.status ? `HTTP ${errOrResponse.status}` : '';
      endpoint = errOrResponse.url || '';
    } else if (typeof errOrResponse === 'object' && errOrResponse !== null) {
      userMsg = errOrResponse.message || errOrResponse.error || userMsg;
      techDetail = errOrResponse.error_detail || errOrResponse.technical || JSON.stringify(errOrResponse, null, 2);
      httpStatus = errOrResponse.status_code ? `HTTP ${errOrResponse.status_code}` : '';
    }

    console.error('[LIS DEVELOPER ERROR]', {
      title: title,
      message: userMsg,
      technical: techDetail,
      endpoint: endpoint,
      raw: errOrResponse
    });

    const safeUserMsg = (userMsg || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const safeTech = (techDetail || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const rawCopy = `[ERROR]: ${userMsg}\n[ENDPOINT]: ${endpoint || window.location.href}\n[STATUS]: ${httpStatus || '-'}\n[DETAIL]:\n${techDetail}`;

    const htmlContent = `
      <div class="text-left space-y-3">
        <p class="text-xs sm:text-sm text-slate-700 font-medium">${safeUserMsg}</p>
        
        <div class="mt-2.5 p-3 rounded-xl bg-slate-900 border border-slate-800 text-slate-200">
          <div class="flex items-center justify-between border-b border-slate-800 pb-1.5 mb-2">
            <span class="text-[11px] font-bold text-amber-400 font-mono flex items-center gap-1.5">
              <span>🔧 Debug Trace (Developer)</span>
              ${httpStatus ? `<span class="px-1.5 py-0.5 rounded text-[10px] bg-rose-950 text-rose-300 border border-rose-800 font-bold">${httpStatus}</span>` : ''}
            </span>
            <button type="button" onclick="navigator.clipboard.writeText(${JSON.stringify(rawCopy)}); LIS.toast('success', 'Detail error disalin ke clipboard!');" class="text-[10px] text-sky-400 hover:text-sky-300 font-mono underline cursor-pointer">
              📋 Salin Error
            </button>
          </div>
          ${endpoint ? `<div class="text-[10px] text-slate-400 font-mono mb-1 truncate"><strong>Endpoint:</strong> ${endpoint}</div>` : ''}
          <pre class="text-[11px] font-mono text-rose-300 overflow-x-auto max-h-48 whitespace-pre-wrap leading-relaxed select-all">${safeTech || 'Tidak ada detail stack trace lanjutan.'}</pre>
        </div>
      </div>
    `;

    if (typeof Swal !== 'undefined') {
      return Swal.fire({
        title: title,
        html: htmlContent,
        icon: 'error',
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Tutup',
        customClass: {
          popup: 'rounded-2xl shadow-2xl max-w-xl w-full',
          confirmButton: 'lis-btn lis-btn-md'
        }
      });
    } else {
      alert(`${title}\n${userMsg}\n\nDetail:\n${techDetail}`);
    }
  },

  /**
   * Helper Fetch JSON yang otomatis mengekstrak error PHP, Database SQL, dan HTTP Exception
   */
  fetchJson: async function(url, options = {}) {
    try {
      const response = await fetch(url, options);
      const text = await response.text();
      let data = null;

      try {
        data = JSON.parse(text);
      } catch (jsonErr) {
        // Respons bukan JSON (kemungkinan fatal error PHP, database error, atau 404/500 HTML dari CodeIgniter)
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, 'text/html');
        
        let heading = doc.querySelector('h1, h4')?.innerText || `Server Error (HTTP ${response.status})`;
        let paragraphs = Array.from(doc.querySelectorAll('p')).map(p => p.innerText.trim()).filter(Boolean);
        let errorDetail = paragraphs.join('\n') || text.substring(0, 1000);

        const err = new Error(heading + (paragraphs[0] ? ': ' + paragraphs[0] : ''));
        err.technical = `[HTML RESPONSE - HTTP ${response.status}]\n${heading}\n${errorDetail}`;
        err.status = response.status;
        err.url = url;
        err.rawText = text;
        throw err;
      }

      if (!response.ok || (data && (data.status === 'error' || data.success === false))) {
        const err = new Error(data.message || `Request gagal dengan kode status HTTP ${response.status}`);
        err.technical = data.error_detail || data.technical || JSON.stringify(data, null, 2);
        err.status = response.status;
        err.url = url;
        err.data = data;
        throw err;
      }

      return data;
    } catch (err) {
      if (!err.url) err.url = url;
      throw err;
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    lucide.createIcons();
  }
});
