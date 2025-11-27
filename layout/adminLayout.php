<?php
function adminLayout($children) {
    $currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>

<link href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


<style>
    
/* Scrollbar hidden for sidebar */
.sidebar::-webkit-scrollbar { display: none; }

/* Dropdown hidden by default */
.has-dropdown > .dropdown { display: none; }
.has-dropdown.active > .dropdown { display: flex; flex-direction: column; }

/* Smooth transitions */
.sidebar, .main-content { transition: all 0.3s ease; }

/* Hide scrollbar for all browsers */
.nav-list::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Opera */
}
.nav-list {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;     /* Firefox */
}

</style>
</head>
<body class="flex h-screen bg-gray-100">

<div id="sidebar" class="sidebar fixed top-0 left-0 h-full bg-[#2c3c8c] w-64 md:w-72 transform -translate-x-full md:translate-x-0 transition-transform border-r border-gray-200 flex flex-col z-50 ">
  <div class="flex items-center justify-between p-4 border-b border-gray-200">
    <div class="flex items-center gap-2">
      <img src="../images/logo.jpg" class="h-10 w-10" alt="Logo">
      <span class="font-bold text-xl hidden md:inline text-white">IMARKET</span>
    </div>
    <button id="sidebarClose" class="md:hidden text-white"><i class='bx bx-x text-3xl'></i></button>
  </div>

  <ul class="nav-list flex-1 mt-4 px-2 overflow-y-auto" style="max-height: 80vh;">
    <?php 
    $modules = [
      ['title'=>'Dashboard','icon'=>'bx-grid-alt','link'=>'warehouse_analytics.php','subs'=>[]],
      [
    'title' => 'Smart Warehousing',
    'icon' => 'bx-package',
    'subs' => [
      ['title' => 'Inbound Logistics (Receiving & Putaway)', 'link' => 'inbound_logistics.php'],
      ['title' => 'Storage & Inventory Management', 'link' => 'storage_inventory.php'],
      ['title' => 'Outbound Logistics (Dispatch & Shipping)', 'link' => 'outbound_logistics.php'],
      ['title' => 'Returns Management (Reverse Logistics)', 'link' => 'returns_management.php'],
    ]
  ],
  [
    'title' => 'Procurement',
    'icon' => 'bx-purchase-tag-alt',
    'subs' => [
      ['title' => 'Supplier Identification & Pre-Qualification', 'link' => 'supplier_identification.php'],
      ['title' => 'Supplier Evaluation & Selection', 'link' => 'supplier_evaluation.php'],
      ['title' => 'Procurement Planning & Requisition', 'link' => 'procurement_planning.php'],
      ['title' => 'Purchase Order (PO) Management', 'link' => 'po_management.php'],
      ['title' => 'Receiving & Quality Assurance', 'link' => 'receiving_quality.php'],
      ['title' => 'Supplier Relationship Management', 'link' => 'supplier_relationship.php'],
      ['title' => 'Payment & Compliance', 'link' => 'payment_compliance.php'],
      ['title' => 'Procurement Reports & Contracts', 'link' => 'procurement_reports.php'],
    ]
  ],
  [
    'title' => 'Asset Lifecycle & Maintenance',
    'icon' => 'bx-recycle',
    'subs' => [
      ['title' => 'Receiving & Logistics Intake', 'link' => 'asset_receiving_logistics.php'],
      ['title' => 'Onboarding & Registration', 'link' => 'asset_onboarding_registration.php'],
      ['title' => 'Deployment & Operational Life', 'link' => 'asset_deployment_lifecycle.php'],
      ['title' => 'Maintenance & Servicing', 'link' => 'asset_maintenance_servicing.php'],
      ['title' => 'End-of-Life & Disposal', 'link' => 'asset_end_of_life_disposal.php'],
    ]
  ],
  [
    'title' => 'Logistics Tracking',
    'icon' => 'bx-map',
    'subs' => [
      ['title' => 'Shipments & Deliveries', 'link' => 'deliveries.php'],
      ['title' => 'Milestones & Status Updates', 'link' => 'milestones.php'],
      // ['title' => 'Logistics Performance', 'link' => 'logistics_performance.php'],
    ]
  ],
  [
    'title' => 'Asset Management',
    'icon' => 'bx-cube-alt',
    'subs' => [
      ['title' => 'Asset Registration & Usage', 'link' => 'asset_registration.php'],
      ['title' => 'Maintenance Scheduling', 'link' => 'maintenance_schedule.php'],
      ['title' => 'Lifecycle & Replacement', 'link' => 'lifecycle_replacement.php'],
    ]
  ],
  [
    'title' => 'Records & Compliance',
    'icon' => 'bx-folder',
    'subs' => [
      ['title' => 'Document Management', 'link' => 'documents.php'],
      // ['title' => 'Audit & Reporting', 'link' => 'audit_reporting.php'],
      // ['title' => 'Archives & Search', 'link' => 'archives.php'],
    ]
    ],
    [
      'title' => 'Project Logistics Tracker',
      'icon' => 'bx-briefcase',
      'subs' => [
        ['title' => 'Project Requirement Planning', 'link' => 'project_requirement_planning.php'],
        ['title' => 'Procurement & Supplier Coordination', 'link' => 'procurement_supplier_coordination.php'],
        ['title' => 'Shipment Scheduling & Route Planning', 'link' => 'shipment_scheduling_route_planning.php'],
        ['title' => 'Execution & Real-Time Tracking', 'link' => 'execution_realtime_tracking.php'],
        ['title' => 'Customs & Regulatory Compliance', 'link' => 'customs_regulatory_compliance.php'],
        ['title' => 'Delivery & Site Coordination', 'link' => 'delivery_site_coordination.php'],
        ['title' => 'Project Performance Monitoring & Closure', 'link' => 'project_performance_monitoring_closure.php'],
      ]
    ],
      ['title'=>'Admin Settings','icon'=>'bx-cog','subs'=>[
          ['title'=>'Users & Roles','link'=>'usersRoles.php'],
          // ['title'=>'System Configuration','link'=>'systemConfiguration.php'],
      ]],
    ];

    foreach($modules as $mod):
      // Check if 'link' key exists to avoid the "Undefined array key" warning
      $isCurrentPage = isset($mod['link']) && ($currentPage == $mod['link']);
      $hasActiveSub = false;
      if (!empty($mod['subs'])) {
          foreach ($mod['subs'] as $sub) {
              if ($currentPage == $sub['link']) {
                  $hasActiveSub = true;
                  break;
              }
          }
      }
    ?>
      <?php if(empty($mod['subs'])): ?>
        <li>
          <a href="<?= $mod['link'] ?>" class="flex items-center gap-6 p-3 rounded-lg text-lg font-medium text-white hover:bg-[#4353a2] <?= $isCurrentPage ? 'bg-[#5c6cb8] mb-2' : '' ?>">
            <i class='bx <?= $mod['icon'] ?> text-2xl text-white'></i>
            <span class="links_name md:inline text-[16px]"><?= $mod['title'] ?></span>
          </a>
        </li>
      <?php else: ?>
        <li class="has-dropdown <?= $hasActiveSub ? 'active' : '' ?>">
          <button class="flex items-center gap-4 w-full p-3 rounded-lg text-lg font-medium text-white hover:bg-[#4353a2] <?= $hasActiveSub ? 'bg-[#5c6cb8] mb-2' : '' ?>">
            <i class='bx <?= $mod['icon'] ?> text-2xl text-white'></i>
            <span class="links_name md:inline text-[16px]"><?= $mod['title'] ?></span>
            <i class='bx bx-chevron-down ml-auto text-xl transition-transform transform <?= $hasActiveSub ? 'rotate-180' : '' ?>'></i>
          </button>
          <div class="dropdown flex-col pl-12 space-y-2 mt-2">
            <?php foreach($mod['subs'] as $sub): ?>
              <a href="<?= $sub['link'] ?>" class="flex items-center gap-3 p-2 rounded-lg text-gray-200 hover:bg-[#4353a2] <?php if($currentPage==$sub['link']) echo 'bg-[#5c6cb8]'; ?>">
                <i class='bx bx-right-arrow-alt text-[12px]'></i><?= $sub['title'] ?>
              </a>
            <?php endforeach; ?>
          </div>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>

<div class="p-4 border-t border-gray-200 flex items-center gap-2">
    <!-- Initials Circle with link -->
    <a href="profile.php" class="h-12 w-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg hover:opacity-80 transition">
        <?php echo strtoupper(substr(preg_replace('/\s+/', '', htmlspecialchars($_SESSION['name'] ?? '')), 0, 2)); ?>
    </a>

    <div class="hidden md:flex flex-col">
        <span class="font-semibold text-lg text-white">
            <?php echo htmlspecialchars($_SESSION['name'] ?? 'Unknown'); ?>
        </span>
        <span class="text-sm text-gray-300">
            <?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'User')); ?>
        </span>
    </div>

    <button id="log_out" class="ml-auto text-gray-200 hover:text-red-300">
        <i class='bx bx-log-out text-3xl'></i>
    </button>
</div>

</div>

<div id="mainContent" class="main-content flex-1 ml-0 md:ml-72 p-4 transition-all">

  <header class="flex justify-between items-center p-4 mb-4 bg-white shadow rounded sticky top-0 z-30">
    <button id="sidebarOpen" class="md:hidden text-gray-600"><i class='bx bx-menu text-3xl'></i></button>

    <div class="flex-1 flex justify-center">
      <div class="relative w-full max-w-md">
        <input type="text" placeholder="Search..." class="w-full pl-3 pr-10 py-2 border rounded focus:ring-2 focus:ring-indigo-500">
        <i class='bx bx-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
      </div>
    </div>

    <div class="flex-1 flex justify-end gap-4 relative">
      <button id="notifBtn" class="relative text-gray-600 text-2xl">
        <i class='bx bx-bell'></i>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1 rounded-full">3</span>
      </button>
      <div id="notifModal" class="hidden absolute right-0 mt-2 w-64 bg-white shadow rounded border border-gray-200 z-50">
        <div class="p-3 border-b font-semibold">Notifications</div>
        <ul>
          <li class="px-3 py-2 hover:bg-gray-100 cursor-pointer">New applicant submitted resume</li>
          <li class="px-3 py-2 hover:bg-gray-100 cursor-pointer">Interview scheduled today</li>
          <li class="px-3 py-2 hover:bg-gray-100 cursor-pointer">System update available</li>
        </ul>
        <div class="text-center p-2 border-t text-sm text-gray-500 cursor-pointer hover:bg-gray-100">View All</div>
      </div>
    </div>
  </header>

  <div>
    <?php echo $children; ?>
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
<script>
// Sidebar open/close
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sidebarOpenBtn = document.getElementById('sidebarOpen');
const sidebarCloseBtn = document.getElementById('sidebarClose');

sidebarOpenBtn?.addEventListener('click', () => {
    sidebar.classList.remove('-translate-x-full');
    const overlay = document.createElement('div');
    overlay.id = 'sidebar-overlay';
    overlay.className = 'fixed inset-0 bg-black opacity-50 z-40 md:hidden';
    document.body.appendChild(overlay);

    overlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.remove();
    });
});

sidebarCloseBtn?.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay')?.remove();
});

// Dropdowns
document.querySelectorAll('.has-dropdown > button').forEach(btn => {
    btn.addEventListener('click', () => {
        const parentLi = btn.parentElement;
        const dropdown = parentLi.querySelector('.dropdown');
        const icon = btn.querySelector('.bx-chevron-down');

        const isOpen = parentLi.classList.contains('active');

        document.querySelectorAll('.has-dropdown.active').forEach(openDropdown => {
            if (openDropdown !== parentLi) {
                openDropdown.classList.remove('active');
                openDropdown.querySelector('.bx-chevron-down').classList.remove('rotate-180');
            }
        });

        if (isOpen) {
            parentLi.classList.remove('active');
            icon.classList.remove('rotate-180');
        } else {
            parentLi.classList.add('active');
            icon.classList.add('rotate-180');
        }
    });
});

// Notifications
const notifBtn = document.getElementById('notifBtn');
const notifModal = document.getElementById('notifModal');
notifBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    notifModal.classList.toggle('hidden');
});
window.addEventListener('click', e => {
    if (!notifBtn.contains(e.target) && !notifModal.contains(e.target)) {
        notifModal.classList.add('hidden');
    }
});

// Logout
async function logoutUser() {
    try {
        const res = await fetch('http://localhost/caplog1/api/logout.php', { method: 'POST' });
        const data = await res.json();
        Toastify({ text: data.message || 'Logged out', duration: 3000 }).showToast();
        if (data.status === 'success') {
            setTimeout(() => window.location.href = 'http://localhost/caplog1', 1000);
        }
    } catch (e) {
        console.error(e);
    }
}
document.getElementById('log_out')?.addEventListener('click', logoutUser);
</script>

</body>
</html>
<?php
}
?>