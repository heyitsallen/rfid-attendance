@extends('layouts.app')

@section('title', 'Management')

@section('content')
<div class="p-6 w-full min-h-screen bg-cover bg-center" 
     style="background-image: url('{{ asset('images/background.jpg') }}');">

@php($active = $active ?? 'management')


<div  x-data="managementPage()" @open-edit.window="openEdit($event.detail)" x-init="init()" class="p-6 w-full">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Management</h1>

        {{-- Quick Actions --}}
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="openAdd('student')"
                class="px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
                + Add Student
            </button>
            <button
                type="button"
                @click="openAdd('faculty')"
                class="px-3 py-2 rounded bg-emerald-600 text-white text-sm hover:bg-emerald-700">
                + Add Faculty
            </button>
            <button
                type="button"
                data-modal-target="checkCardModal"
                data-modal-toggle="checkCardModal"
                class="px-3 py-2 rounded bg-amber-500 text-white text-sm hover:bg-amber-600">
                Check Card
            </button>
        </div>
    </div>

    {{-- Filter: Student / Faculty --}}
    <div class="mb-4 flex items-center gap-2">
        <button
            :class="tab === 'student' ? 'bg-blue-100 text-blue-700 border-blue-400' : 'bg-white text-gray-700 hover:bg-gray-50'"
            @click="tab = 'student'"
            class="px-3 py-1.5 border rounded-md text-sm">
            Students
        </button>
        <button
            :class="tab === 'faculty' ? 'bg-blue-100 text-blue-700 border-blue-400' : 'bg-white text-gray-700 hover:bg-gray-50'"
            @click="tab = 'faculty'"
            class="px-3 py-1.5 border rounded-md text-sm">
            Faculty
        </button>
    </div>

    {{-- TABLES --}}
    <div x-show="tab === 'student'">
        @include('admin.partials.table-users', ['rows' => $students, 'role' => 'student'])
    </div>
    <div x-show="tab === 'faculty'">
        @include('admin.partials.table-users', ['rows' => $faculties, 'role' => 'faculty'])
    </div>

    {{-- ======= MODALS ======= --}}

    {{-- Add User Modal --}}
    <div id="addUserModal" x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                <span x-text="addRole === 'student' ? 'Add Student' : 'Add Faculty'"></span>
            </h3>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" :value="addRole">

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">First name</label>
                        <input name="firstname" required class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Middle name</label>
                        <input name="middlename" class="w-full border rounded p-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm mb-1">Last name</label>
                    <input name="lastname" required class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" name="email" required class="w-full border rounded p-2">
                </div>

                <div x-show="addRole === 'student'">
                    <label class="block text-sm mb-1">Student No.</label>
                    <input name="student_id" class="w-full border rounded p-2">
                </div>

                <div x-show="addRole === 'faculty'">
                    <label class="block text-sm mb-1">Employee No.</label>
                    <input name="employee_no" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm mb-1">Password</label>
                    <input type="password" name="password" required class="w-full border rounded p-2">
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="addOpen=false" class="px-3 py-2 border rounded">Cancel</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Check Card Modal --}}
    <div id="checkCardModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Check Card</h3>
            <div class="space-y-3">
                <input id="checkUid" placeholder="Enter Card UID (HEX)" class="w-full border rounded p-2">
                <div id="checkResult" class="text-sm text-gray-700 dark:text-gray-200"></div>
                <div class="flex justify-end gap-2">
                    <button data-modal-hide="checkCardModal" class="px-3 py-2 border rounded">Close</button>
                    <button id="btnCheckCard" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Check</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit User Modal (re-usable) --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow w-full max-w-3xl p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Edit User</h3>

            <form :action="editFormAction" method="POST" class="space-y-4">
                @csrf @method('PUT')

                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm mb-1">First name</label>
                        <input name="firstname" x-model="edit.firstname" required class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Middle name</label>
                        <input name="middlename" x-model="edit.middlename" class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Last name</label>
                        <input name="lastname" x-model="edit.lastname" required class="w-full border rounded p-2">
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Email</label>
                        <input type="email" name="email" x-model="edit.email" required class="w-full border rounded p-2">
                    </div>
                    <div x-show="edit.role==='student'">
                        <label class="block text-sm mb-1">Student No.</label>
                        <input name="student_no" x-model="edit.student_no" class="w-full border rounded p-2">
                    </div>
                    <div x-show="edit.role==='faculty'">
                        <label class="block text-sm mb-1">Employee No.</label>
                        <input name="employee_no" x-model="edit.employee_no" class="w-full border rounded p-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm mb-1">Status</label>
                    <select name="status" x-model="edit.status" class="w-full border rounded p-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                {{-- Card area --}}
                <div class="mt-6">
                    <h4 class="font-medium mb-2">Cards</h4>

                    {{-- Link new card to current school year --}}
                    <div class="flex items-end gap-2 mb-3">
       <div class="flex-1">
  <label class="block text-sm mb-1">New Card UID (HEX)</label>

  <div class="flex items-center gap-2">
    <input
      x-model="link.uid"
      :class="flash ? 'ring-2 ring-emerald-400 ring-offset-2' : ''"
      placeholder="e.g., 04A1BC23"
      class="w-full border rounded p-2 transition"
    >
    <button
      type="button"
      @click="link.uid=''; pendingUid=null"
      class="px-2 py-1 border rounded text-xs hover:bg-gray-50"
    >Clear</button>
  </div>

  <template x-if="pendingUid">
    <button
      type="button"
      @click="link.uid=pendingUid; pendingUid=null; flash=true; setTimeout(()=>flash=false,600)"
      class="mt-1 text-xs text-blue-600 hover:underline"
    >
      Use scanned UID (<span x-text="pendingUid"></span>)
    </button>
  </template>
</div>

                    </div>

                    {{-- Card history --}}
                    <div class="overflow-auto border rounded">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="text-left">
                                    <th class="px-3 py-2">UID</th>
                                    <th class="px-3 py-2">School Year</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Issued</th>
                                    <th class="px-3 py-2">Revoked</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                          <tbody>
  <template x-for="c in edit.cards" :key="c.id">
    <tr class="border-t">
      <!-- UID (readonly; safer not to edit UID inline) -->
      <td class="px-3 py-2" x-text="c.uid"></td>

      <!-- School Year -->
      <td class="px-3 py-2">
        <template x-if="!c._editing">
          <span x-text="c.school_year?.name ?? '-'"></span>
        </template>
        <template x-if="c._editing">
          <select x-model.number="c._temp.school_year_id" class="border rounded p-1">
            <option value="">—</option>
            @foreach ($schoolYears as $sy)
              <option value="{{ $sy->id }}">{{ $sy->name }}</option>
            @endforeach
          </select>
        </template>
      </td>

      <!-- Status -->
      <td class="px-3 py-2">
        <template x-if="!c._editing">
          <span class="px-2 py-1 rounded text-xs"
                :class="c.status==='active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
            <span x-text="c.status"></span>
          </span>
        </template>
        <template x-if="c._editing">
          <select x-model="c._temp.status" class="border rounded p-1">
            <option value="1">active</option>
            <option value="0">inactive</option>
          </select>
        </template>
      </td>

      <!-- Issued -->
      <td class="px-3 py-2">
        <template x-if="!c._editing">
          <span x-text="c.issued_at ?? '-'"></span>
        </template>
        <template x-if="c._editing">
          <input type="datetime-local" x-model="c._temp.issued_at" class="border rounded p-1">
        </template>
      </td>

      <!-- Revoked -->
      <td class="px-3 py-2">
        <template x-if="!c._editing">
          <span x-text="c.revoked_at ?? '-'"></span>
        </template>
        <template x-if="c._editing">
          <input type="datetime-local" x-model="c._temp.revoked_at" class="border rounded p-1">
        </template>
      </td>

      <!-- Actions -->
      <td class="px-3 py-2 text-right">
        <div class="inline-flex gap-2">
          <template x-if="!c._editing">
            <div class="inline-flex gap-2">
              <button type="button"
                      @click="startEditCard(c)"
                      class="px-2 py-1 border rounded text-xs hover:bg-gray-50">
                Edit
              </button>

              <!-- quick toggle still available -->
              <form :action="`{{ url('/admin/cards') }}/${c.id}`" method="POST" class="inline">
                @csrf @method('PUT')
                <input type="hidden" name="status" :value="c.status === 'active' ? 'inactive' : 'active'">
                <button class="px-2 py-1 rounded text-xs"
                        :class="c.status==='active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                  <span x-text="c.status==='active' ? 'Deactivate' : 'Activate'"></span>
                </button>
              </form>
            </div>
          </template>

          <template x-if="c._editing">
            <div class="inline-flex gap-2">
              <button type="button"
                      @click="saveCard(c)"
                      class="px-2 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
                Save
              </button>
              <button type="button"
                      @click="cancelEditCard(c)"
                      class="px-2 py-1 border rounded text-xs hover:bg-gray-50">
                Cancel
              </button>
            </div>
          </template>
        </div>
      </td>
    </tr>
  </template>

  <tr x-show="!edit.cards || edit.cards.length===0">
    <td colspan="6" class="px-3 py-2 text-gray-500">No cards yet.</td>
  </tr>
</tbody>

                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="editOpen=false" class="px-3 py-2 border rounded">Close</button>
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- PARTIAL: table-users --}}
@push('partials')
@endpush

{{-- Alpine --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
function managementPage() {
  return {
    tab: 'student',
    addOpen: false,
    addRole: 'student',

    editOpen: false,
    editFormAction: '',
    edit: { id:null, role:null, firstname:'', middlename:'', lastname:'', email:'', student_no:'', employee_no:'', status:'active', cards: [] },

    link: { uid:'', school_year_id: '{{ $currentSY->id ?? '' }}' },

    // scanner auto-fill
    deviceSerial: @js(config('rfid.enroll_serial','admin')),
    _scanPoll: null,
    lastScanUid: null,
    pendingUid: null,
    flash: false,

    // card inline-edit helpers
    schoolYears: @js($schoolYears->map(fn($sy) => ['id' => $sy->id, 'name' => $sy->name])),

    init() {
      this._scanPoll = setInterval(() => this.pollScan(), 1000);
    },

    async pollScan() {
      if (!this.editOpen) return;
      try {
        const url = `{{ route('admin.scans.last') }}?device=${encodeURIComponent(this.deviceSerial)}`;
        const res = await fetch(url, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }, cache:'no-store' });
        const { uid } = await res.json();
        if (!uid || uid === this.lastScanUid) return;
        this.lastScanUid = uid;

        if ((this.link.uid ?? '').trim() === '') {
          this.link.uid = uid;              // pop in once
          this.pendingUid = null;
          this.flash = true; setTimeout(()=>this.flash=false,600);
        } else {
          this.pendingUid = uid;            // suggest only
        }
      } catch(_) {}
    },

    openAdd(role) {
      this.addRole = role;
      this.addOpen = true;
    },

    openEdit(row) {
      this.edit = JSON.parse(JSON.stringify(row));
      this.editFormAction = `{{ url('/admin/users') }}/${row.id}`;
      this.editOpen = true;

      // reset scanner helpers
      this.link.uid = '';
      this.lastScanUid = null;
      this.pendingUid = null;
      this.flash = false;
    },

    linkCard() {
      if (!this.link.uid || !this.link.school_year_id || !this.edit?.id) return;
      fetch(`{{ route('admin.cards.link') }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({
          uid: this.link.uid,
          user_id: this.edit.id,
          school_year_id: this.link.school_year_id
        })
      }).then(() => location.reload());
    },

    startEditCard(c) {
      c._editing = true;
      c._temp = {
        status: c.status ?? 'active',
        school_year_id: c.school_year?.id ?? '',
        issued_at: c.issued_at ? c.issued_at.replace(' ', 'T').slice(0,16) : '',
        revoked_at: c.revoked_at ? c.revoked_at.replace(' ', 'T').slice(0,16) : ''
      };
    },

    cancelEditCard(c) {
      c._editing = false;
      c._temp = null;
    },

    schoolYearNameById(id) {
      const s = this.schoolYears.find(x => Number(x.id) === Number(id));
      return s ? s.name : '-';
    },

    async saveCard(c) {
      const payload = {
        _method: 'PUT',                       // <-- match your PUT route
        status: c._temp.status,
        school_year_id: c._temp.school_year_id || null,
        issued_at: c._temp.issued_at || null,
        revoked_at: c._temp.revoked_at || null,
      };

      try {
        const res = await fetch(`{{ url('/admin/cards') }}/${c.id}`, {
          method: 'POST',                     // POST + _method spoof = PUT
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
          if (res.ok) location.reload();
          return;
        }
        const data = await res.json();
        if (!res.ok || data?.ok === false) throw new Error(data?.message || 'Update failed');

        // optimistic UI update
        c.status = payload.status;
        c.school_year = payload.school_year_id
          ? { id: Number(payload.school_year_id), name: this.schoolYearNameById(payload.school_year_id) }
          : null;
        c.issued_at = payload.issued_at ? payload.issued_at.replace('T',' ') : null;
        c.revoked_at = payload.revoked_at ? payload.revoked_at.replace('T',' ') : null;

        c._editing = false;
        c._temp = null;
      } catch (e) {
        alert(e.message || 'Failed to save card changes.');
      }
    },
  }
}
</script>





@endsection
