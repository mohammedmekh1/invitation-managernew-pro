(function($){
  function fetchStats(){
    $.post(IM_AJAX.url, { action:'im_get_stats', _ajax_nonce: IM_AJAX.nonce }, function(res){
      if(res && res.success){
        $('#im-stat-invitations').text(res.data.invitations);
        $('#im-stat-guests').text(res.data.guests);
        $('#im-stat-events').text(res.data.events);
        $('#im-stat-scans').text(res.data.scans);
      }
    });
  }

  function renderActivity(items){
    var $wrap = $('#im-activity').empty();
    if(!items || !items.length){
      $wrap.append('<div class="empty">لا توجد نشاطات بعد.</div>');
      return;
    }
    items.forEach(function(it){
      var name = it.guest_name ? it.guest_name : '—';
      var time = it.created_at ? it.created_at : '';
      var result = it.scan_result === 'success' ? '<span class="badge success">نجاح</span>' : '<span class="badge">—</span>';
      $wrap.append(
        '<div class="activity-item">' +
          '<div class="left">🔍 <strong>'+ name +'</strong></div>' +
          '<div class="right">'+ result +' <span style="opacity:.7;margin-inline-start:8px">'+ time +'</span></div>' +
        '</div>'
      );
    });
  }

  function fetchActivity(){
    $.post(IM_AJAX.url, { action:'im_get_activity', _ajax_nonce: IM_AJAX.nonce }, function(res){
      if(res && res.success){
        renderActivity(res.data);
      }
    });
  }

  $(document).ready(function(){
    fetchStats();
    fetchActivity();
  });
})(jQuery);
