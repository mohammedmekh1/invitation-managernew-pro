<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="im-wrap">
  <h1 class="im-title">لوحة التحكم</h1>

  <!-- إحصائيات -->
  <div class="im-grid stats">
    <div class="card stat" title="إحصائيات الحساب">
      <div class="icon">📊</div>
      <div class="meta">
        <div class="label">الدعوات</div>
        <div class="value" id="im-stat-invitations">—</div>
      </div>
    </div>
    <div class="card stat" title="عدد المدعوين">
      <div class="icon">🧑‍🤝‍🧑</div>
      <div class="meta">
        <div class="label">المدعوون</div>
        <div class="value" id="im-stat-guests">—</div>
      </div>
    </div>
    <div class="card stat" title="عدد المناسبات">
      <div class="icon">📅</div>
      <div class="meta">
        <div class="label">المناسبات</div>
        <div class="value" id="im-stat-events">—</div>
      </div>
    </div>
    <div class="card stat" title="عدد عمليات المسح">
      <div class="icon">🔍</div>
      <div class="meta">
        <div class="label">عمليات المسح</div>
        <div class="value" id="im-stat-scans">—</div>
      </div>
    </div>
  </div>

  <!-- إجراءات سريعة -->
  <div class="quick-actions">
    <h2>إجراءات سريعة</h2>
    <div class="im-grid qa">
      <a class="card qa-btn" href="<?php echo admin_url('post-new.php?post_type=im_event'); ?>" title="إضافة مناسبة جديدة">➕ مناسبة</a>
      <a class="card qa-btn" href="<?php echo admin_url('admin.php?page=im-dashboard&tab=add-guest'); ?>" title="إضافة ضيف جديد">➕ ضيف</a>
      <a class="card qa-btn" href="<?php echo admin_url('admin.php?page=im-dashboard&tab=import'); ?>" title="استيراد المدعوين">⬆️ استيراد</a>
      <a class="card qa-btn" href="<?php echo admin_url('admin.php?page=im-qr'); ?>" title="فتح ماسح رمز الدعوة">📱 ماسح QR</a>
    </div>
  </div>

  <!-- النشاط الأخير -->
  <div class="recent">
    <div class="recent-header">
      <h2>النشاط الأخير</h2>
      <div class="tabs">
        <button class="tab active" data-type="scans" title="عرض آخر عمليات المسح">عمليات المسح</button>
      </div>
    </div>
    <div id="im-activity" class="activity-list">
      <div class="empty">لا توجد نشاطات بعد.</div>
    </div>
  </div>
</div>
