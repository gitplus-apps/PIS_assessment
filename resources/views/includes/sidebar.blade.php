<!-- partial:partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    @php
      $user = Auth::user();
    @endphp

    {{-- Non-SDM users see their permitted modules --}}
    @if($user->usertype !== 'SDM')
      @php
        $parent = DB::table('tblmodule')
          ->select([
            'tbluser_module_privileges.mod_read',
            'tbluser_module_privileges.userid',
            'tblmodule.mod_name',
            'tblmodule.mod_label',
            'tblmodule.mod_url',
            'tblmodule.mod_icon',
            'tblmodule.mod_id',
          ])
          ->join(
            'tbluser_module_privileges',
            'tblmodule.mod_id',
            '=',
            'tbluser_module_privileges.mod_id'
          )
          // match this user’s ID and school
          ->where('tbluser_module_privileges.userid', $user->userid)
          ->where('tbluser_module_privileges.school_code', $user->school_code)
          // require read privilege
          ->where('tbluser_module_privileges.mod_read', 1)
          // always only active modules
          ->where('tblmodule.mod_status', 1)
          // pick the right “type” of module
          ->when($user->usertype === 'ADM', function($q) {
            return $q->where('tblmodule.system_mod', 1);
          })
          ->when($user->usertype === 'STA', function($q) {
            return $q->where('tblmodule.teacher_mod', 1);
          })
          ->when($user->usertype === 'STU', function($q) {
            return $q->where('tblmodule.student_mod', 1);
          })
          // final ordering
          ->orderBy('tblmodule.arrange', 'ASC')
          ->get();
        // temporary debug—how many did we get?
      @endphp

      @foreach($parent as $mod)
        <li class="nav-item {{ Route::currentRouteName() === strtolower($mod->mod_name) ? 'active' : '' }}">
          <a class="nav-link" href="{{ url($mod->mod_url) }}">
            <i class="mr-3 {{ $mod->mod_icon }}"></i>
            <span>{{ $mod->mod_label }}</span>
          </a>
        </li>
      @endforeach
    @endif

    {{-- SDM users have a fixed dashboard menu --}}
    @if($user->usertype === 'SDM')
      <li class="nav-item {{ Route::currentRouteName()==='dashboard' ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('/') }}">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="nav-item {{ Route::currentRouteName()==='schools' ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('super_admin/schools') }}">
          <i class="fas fa-building"></i>
          <span>Schools</span>
        </a>
      </li>

      <li class="nav-item {{ Route::currentRouteName()==='student' ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('super_admin/student') }}">
          <i class="fas fa-user"></i>
          <span>Student</span>
        </a>
      </li>

      <li class="nav-item {{ Route::currentRouteName()==='staff' ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('super_admin/staff') }}">
          <i class="fas fa-fw fa-users"></i>
          <span>Staff</span>
        </a>
      </li>

      <li class="nav-item {{ Route::currentRouteName()==='messaging' ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('super_admin/messaging') }}">
          <i class="fas fa-comment-alt"></i>
          <span>Messaging</span>
        </a>
      </li>

      <li class="nav-item {{ Route::currentRouteName()==='settings' ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('super_admin/settings') }}">
          <i class="fas fa-cogs"></i>
          <span>Settings</span>
        </a>
      </li>
    @endif
  </ul>
</nav>

