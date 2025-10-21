<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/supplies.css') }}">
<link rel="stylesheet" href="{{ asset('css/help.css') }}">
<link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container">
    @include('layouts.core.sidebar')
    <div class="details">
        @include('layouts.core.header')
        <div class="supplies-container">
            <div class="supplies-header">
                <h1 class="supplies-title">
                    <i class="fas fa-eye"></i>
                    Help Request Details
                </h1>
                <div class="help-meta">
                    <span class="badge badge-{{ $helpRequest->priority_color }}">{{ ucfirst($helpRequest->priority) }}</span>
                    <span class="badge badge-{{ $helpRequest->status_color }}">{{ ucfirst(str_replace('_', ' ', $helpRequest->status)) }}</span>
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <span class="help-date" style="color: #000; font-weight: 600;">{{ $helpRequest->created_date }}</span>
                        @if($helpRequest->updated_at != $helpRequest->created_at)
                            <span class="help-date" style="color: #000; font-weight: 600;">Last updated: {{ $helpRequest->updated_date }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="help-detail-content">
                <div class="header-actions" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    @if(auth()->user()->isAdmin() || ($helpRequest->user_id === auth()->id() && $helpRequest->status === 'pending'))
                        <a href="{{ route('client.help.edit', $helpRequest->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('client.help.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

                <div class="supplies-table-container">
                    <div class="help-card">
                        <div class="card-section">
                            <h3><i class="fas fa-user"></i> Submitted by</h3>
                            <div class="user-info-detailed">
                                <img src="{{ $helpRequest->user->avatar_url }}" alt="{{ $helpRequest->user->name }}" class="user-avatar">
                                <div>
                                    <strong>{{ $helpRequest->user->name }}</strong>
                                    <p>{{ $helpRequest->user->email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-section">
                            <h3><i class="fas fa-heading"></i> Subject</h3>
                            <p class="subject">{{ $helpRequest->subject }}</p>
                        </div>

                        <div class="card-section">
                            <h3><i class="fas fa-file-alt"></i> Description</h3>
                            <div class="description">{{ $helpRequest->description }}</div>
                        </div>

                        @if($helpRequest->admin_response)
                        <div class="card-section">
                            <h3><i class="fas fa-reply"></i> Admin Response</h3>
                            <div class="admin-response">
                                {{ $helpRequest->admin_response }}
                                @if($helpRequest->assignedTo)
                                    <div class="assigned-info">
                                        <strong>Assigned to:</strong> {{ $helpRequest->assignedTo->name }}
                                    </div>
                                @endif
                                @if($helpRequest->resolved_at)
                                    <div class="resolved-info">
                                        <strong>Resolved on:</strong> {{ $helpRequest->resolved_at->format('d F, Y h:i A') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
