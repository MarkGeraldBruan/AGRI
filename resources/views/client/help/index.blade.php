<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/help.css') }}">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
        <div class="container">
        @include('layouts.core.sidebar')
        
        <div class="details">
            @include('layouts.core.header')
            <div class="help-container">
                <div class="help-header">
                    <h1><ion-icon name="help-circle-outline"></ion-icon> Help & Support</h1>
                    @if(!auth()->user()->isAdmin())
                    <a href="{{ route('client.help.create') }}" class="btn btn-primary">
                        <ion-icon name="add-outline"></ion-icon> Create Request
                    </a>
                    @endif
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="help-requests">
                    @if($helpRequests->count() > 0)
                        @foreach($helpRequests as $request)
                        <div class="help-card">
                            <div class="help-card-header">
                                <div class="help-info">
                                    <h3>{{ $request->subject }}</h3>
                                    @if(auth()->user()->isAdmin())
                                        <span class="user-name">by {{ $request->user->name }}</span>
                                    @endif
                                    <span class="help-date">{{ $request->created_date }}</span>
                                </div>
                                <div class="help-badges">
                                    <span class="badge badge-{{ $request->priority_color }}">{{ ucfirst($request->priority) }}</span>
                                    <span class="badge badge-{{ $request->status_color }}">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</span>
                                </div>
                            </div>
                            <div class="help-card-body">
                                <p>{{ Str::limit($request->description, 150) }}</p>
                                @if($request->admin_response)
                                    <div class="admin-response">
                                        <strong>Admin Response:</strong>
                                        <p>{{ Str::limit($request->admin_response, 100) }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="help-card-actions">
                                <a href="{{ route('client.help.show', $request->id) }}" class="btn btn-sm btn-outline">View Details</a>
                                @if(auth()->user()->isAdmin() || ($request->user_id === auth()->id() && $request->status === 'pending'))
                                    <a href="{{ route('client.help.edit', $request->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @endif
                                @if(auth()->user()->isAdmin() || ($request->user_id === auth()->id() && $request->status === 'pending'))
                                    <button class="btn btn-sm btn-danger" onclick="deleteRequest({{ $request->id }})">Delete</button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <ion-icon name="help-circle-outline"></ion-icon>
                            <h3>No help requests found</h3>
                            @if(!auth()->user()->isAdmin())
                                <p>Submit your first help request to get started.</p>
                                <a href="{{ route('client.help.create') }}" class="btn btn-primary">Submit Request</a>
                            @else
                                <p>No help requests have been submitted yet.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<script>
function deleteRequest(id) {
    if (confirm('Are you sure you want to delete this help request?')) {
        fetch(`/client/help/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>