@props(['rows', 'role'])

<div class="overflow-hidden border rounded">
    <table class="min-w-full text-sm">
        <thead class="bg-blue-500 text-gray-50">
            <tr class="text-left">
                <th class="px-3 py-2">Name</th>
                <th class="px-3 py-2">Email</th>
                @if($role === 'student') <th class="px-3 py-2">Student No.</th> @endif
                @if($role === 'faculty') <th class="px-3 py-2">Employee No.</th> @endif
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $u)
            <tr x-data="{ open:false }" class="border-t">
                <td class="px-3 py-2">
                    <button @click="open=!open" class="inline-flex items-center gap-2">
                        <svg x-show="!open" class="w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"/></svg>
                        <svg x-show="open" class="w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path d="M14.77 12.79a.75.75 0 01-1.06-.02L10 9.06l-3.71 3.71a.75.75 0 11-1.06-1.06l4.24-4.24a.75.75 0 011.06 0l4.24 4.24c.28.29.28.76 0 1.06z"/></svg>
                        <span>{{ $u->firstname }} {{ $u->lastname }}</span>
                    </button>
                </td>
                <td class="px-3 py-2">{{ $u->email }}</td>
                @if($role === 'student') <td class="px-3 py-2">{{ $u->student_no }}</td> @endif
                @if($role === 'faculty') <td class="px-3 py-2">{{ $u->employee_no }}</td> @endif
                <td class="px-3 py-2 capitalize">
                    <span class="px-2 py-1 rounded text-xs {{ $u->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $u->status }}
                    </span>
                </td>
                <td class="px-3 py-2 text-right">
                    <div class="inline-flex gap-2">
                        
                 <button
  @click="$dispatch('open-edit', {
    id: @js($u->id),
    role: @js($u->role),
    firstname: @js($u->firstname),
    middlename: @js($u->middlename),
    lastname: @js($u->lastname),
    email: @js($u->email),
    student_no: @js($u->student_no),
    employee_no: @js($u->employee_no),
    status: @js($u->status),
    cards: @js(
      $u->cards->load('schoolYear')->map(fn($c)=>[
        'id'=>$c->id,'uid'=>$c->uid,'status'=>$c->status,
        'issued_at'=>$c->issued_at,'revoked_at'=>$c->revoked_at,
        'school_year'=> $c->schoolYear ? ['id'=>$c->schoolYear->id, 'name'=>$c->schoolYear->name] : null,
      ])
    )
  })"
  class="px-2 py-1 border rounded text-xs hover:bg-gray-50">
  Edit
</button>



                        

<form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" 
      onsubmit="return confirm('Are you sure to delete this user?')">
    @csrf 
    @method('PATCH')
    <button class="px-2 py-1 border rounded text-xs text-yellow-600 hover:bg-yellow-50">
        Delete
    </button>
</form>
                    </div>
                </td>

                {{-- Collapsible details row --}}
                <td colspan="6" class="px-3 py-2 text-sm text-gray-600" x-show="open">
                    <div class="mt-2 grid sm:grid-cols-3 gap-3">
                        <div><span class="text-gray-500">Role:</span> {{ ucfirst($u->role) }}</div>
                        <div><span class="text-gray-500">Created:</span> {{ $u->created_at->format('Y-m-d') }}</div>
                        <div><span class="text-gray-500">Updated:</span> {{ $u->updated_at->format('Y-m-d') }}</div>
                    </div>
                    <div class="mt-3">
                        <span class="text-gray-500">Cards:</span>
                        @forelse ($u->cards as $card)
                            <span class="ml-2 inline-flex items-center gap-2 px-2 py-1 rounded text-xs border">
                                {{ $card->uid }}
                                <span class="capitalize {{ $card->status==='active' ? 'text-green-700' : 'text-gray-600' }}">({{ $card->status }})</span>
                            </span>
                        @empty
                            <span class="ml-2 text-gray-500">No cards.</span>
                        @endforelse
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No {{ $role }}s found.</td></tr>
        @endforelse
        </tbody>

    </table>

</div>
