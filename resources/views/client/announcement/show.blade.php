<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Announcement</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/announcement.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container">
        @include('layouts.core.sidebar')
        <div class="details">
            @include('layouts.core.header')

            <div class="form-container">
                <!-- Header Section -->
                <div class="form-header">
                    <a href="{{ route('client.announcement.index') }}" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        Back to Announcements
                    </a>
                    <h1 class="form-title">
                        <i class="fas fa-bullhorn"></i>
                        Announcement Details
                    </h1>
                </div>

                <!-- Announcement Details Card -->
                <div class="form-content" style="padding: 30px;">
                    <div class="announcement-header" style="border-bottom: 2px solid #e9ecef; padding-bottom: 20px; margin-bottom: 25px;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <h2 style="color: #296218; font-size: 28px; margin: 0;">{{ $announcement->title }}</h2>
                            <span class="status-badge {{ $announcement->status === 'published' ? 'status-published' : 'status-draft' }}">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                {{ ucfirst($announcement->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="details-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <div class="detail-item">
                            <div style="display: flex; align-items: center; gap: 8px; color: #6c757d; font-size: 14px; margin-bottom: 5px;">
                                <i class="fas fa-calendar-plus"></i>
                                <span style="font-weight: 600;">Date Created</span>
                            </div>
                            <div style="color: #495057; font-size: 15px;">
                                {{ $announcement->created_at->format('F d, Y') }}
                                <span style="color: #6c757d;">at {{ $announcement->created_at->format('g:i A') }}</span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div style="display: flex; align-items: center; gap: 8px; color: #6c757d; font-size: 14px; margin-bottom: 5px;">
                                <i class="fas fa-calendar-check"></i>
                                <span style="font-weight: 600;">Last Updated</span>
                            </div>
                            <div style="color: #495057; font-size: 15px;">
                                {{ $announcement->updated_at->format('F d, Y') }}
                                <span style="color: #6c757d;">at {{ $announcement->updated_at->format('g:i A') }}</span>
                            </div>
                        </div>

                        @if($announcement->event_date)
                        <div class="detail-item">
                            <div style="display: flex; align-items: center; gap: 8px; color: #6c757d; font-size: 14px; margin-bottom: 5px;">
                                <i class="fas fa-calendar-day"></i>
                                <span style="font-weight: 600;">Event Date</span>
                            </div>
                            <div style="color: #495057; font-size: 15px;">
                                {{ $announcement->event_date->format('F d, Y') }}
                            </div>
                        </div>
                        @endif

                        @if($announcement->creator)
                        <div class="detail-item">
                            <div style="display: flex; align-items: center; gap: 8px; color: #6c757d; font-size: 14px; margin-bottom: 5px;">
                                <i class="fas fa-user"></i>
                                <span style="font-weight: 600;">Created By</span>
                            </div>
                            <div style="color: #495057; font-size: 15px;">
                                {{ $announcement->creator->name }}
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="content-section" style="background: #f8f9fa; padding: 25px; border-radius: 8px; border-left: 4px solid #296218;">
                        <div style="display: flex; align-items: center; gap: 8px; color: #495057; font-weight: 600; margin-bottom: 15px;">
                            <i class="fas fa-align-left"></i>
                            <span>Content</span>
                        </div>
                        <div style="color: #495057; line-height: 1.8; white-space: pre-wrap; font-size: 15px;">
                            {{ $announcement->content }}
                        </div>
                    </div>

                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center;">
                        <button type="button" 
                                class="btn btn-danger" 
                                onclick="confirmDelete({{ $announcement->id }}, '{{ addslashes($announcement->title) }}')">
                            <i class="fas fa-trash"></i>
                            Delete Announcement
                        </button>
                        
                        <div style="display: flex; gap: 10px;">
                            <a href="{{ route('client.announcement.edit', $announcement->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                            <a href="{{ route('client.announcement.index') }}" class="btn btn-secondary">
                                <i class="fas fa-list"></i>
                                View All
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>