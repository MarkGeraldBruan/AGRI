<form action="{{ $route }}" method="POST">
    @csrf
    @if(isset($announcement))
        @method('PUT')
    @endif
    
    <div class="form-grid">
        <!-- Title -->
        <div class="form-group full-width">
            <label for="title" class="form-label required">Title</label>
            <div class="input-group">
                <i class="fas fa-heading"></i>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-input" 
                       value="{{ old('title', $announcement->title ?? '') }}"
                       placeholder="Enter announcement title"
                       required>
            </div>
            @error('title')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- Status -->
        <div class="form-group">
            <label for="status" class="form-label required">Status</label>
            <select id="status" 
                    name="status" 
                    class="form-select" 
                    required>
                <option value="draft" {{ old('status', $announcement->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $announcement->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
            </select>
            @error('status')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- Event Date -->
        <div class="form-group">
            <label for="event_date" class="form-label">Event Date (Optional)</label>
            <input type="date" 
                   id="event_date" 
                   name="event_date" 
                   class="form-input" 
                   value="{{ old('event_date', isset($announcement) && $announcement->event_date ? $announcement->event_date->format('Y-m-d') : '') }}">
            @error('event_date')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- Content -->
        <div class="form-group full-width">
            <label for="content" class="form-label required">Content</label>
            <textarea id="content" 
                      name="content" 
                      class="form-input form-textarea" 
                      rows="8"
                      placeholder="Enter announcement content..."
                      required>{{ old('content', $announcement->content ?? '') }}</textarea>
            @error('content')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <a href="{{ route('client.announcement.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            Cancel
        </a>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i>
            {{ isset($announcement) ? 'Update Announcement' : 'Create Announcement' }}
        </button>
    </div>
</form>