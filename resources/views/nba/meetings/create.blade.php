@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-calendar-plus me-2 text-primary"></i>Schedule NBA Meeting</h1>
        <p class="text-muted small mb-0">Schedule an NBA Accreditation meeting and invite faculty members</p>
    </div>
    <div>
        <a href="{{ route('nba.meetings.index') }}" class="btn btn-sm btn-outline-secondary fw-medium">&larr; Back to Meetings</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('nba.meetings.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-navy">Meeting Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. NBA Criterion 5 Review Meeting" value="{{ old('title') }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @error
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-navy">Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror" value="{{ old('scheduled_at') }}" required>
                    @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @error
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-navy">Venue / Location</label>
                    <input type="text" name="venue" class="form-control" placeholder="e.g. CSE Conference Hall / Online" value="{{ old('venue') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-navy">Agenda</label>
                    <input type="text" name="agenda" class="form-control" placeholder="Key agenda points..." value="{{ old('agenda') }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-navy">Description / Notes</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-navy">Invite Attendees <span class="text-danger">*</span></label>
                    <select name="attendees[]" class="form-select @error('attendees') is-invalid @enderror" multiple required style="height: 140px;">
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->id }}" {{ in_array($faculty->id, old('attendees', [])) ? 'selected' : '' }}>
                                {{ $faculty->name }} ({{ $faculty->designation }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted">Hold Ctrl / Cmd to select multiple attendees.</div>
                    @error('attendees') <div class="invalid-feedback">{{ $message }}</div> @error
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('nba.meetings.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-psg-primary px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Schedule Meeting</button>
            </div>
        </form>
    </div>
</div>
@endsection
