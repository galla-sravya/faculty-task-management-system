@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-check2-circle me-2 text-success"></i>Complete NBA Meeting</h1>
        <p class="text-muted small mb-0">Record attendance and publish Minutes of Meeting (MoM) for: {{ $meeting->title }}</p>
    </div>
    <div>
        <a href="{{ route('nba.meetings.show', $meeting) }}" class="btn btn-sm btn-outline-secondary fw-medium">&larr; Back to Meeting Details</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('nba.meetings.complete', $meeting) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-semibold text-navy">Minutes of Meeting (MoM) <span class="text-danger">*</span></label>
                <textarea name="minutes" class="form-control @error('minutes') is-invalid @enderror" rows="6" placeholder="Enter meeting proceedings, action items, decisions..." required>{{ old('minutes', $meeting->minutes) }}</textarea>
                @error('minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold text-navy">Attendance Status</label>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" style="font-size: 0.88rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>Faculty Name</th>
                                <th>Designation</th>
                                <th class="text-center" style="width: 200px;">Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meeting->attendees as $att)
                            <tr>
                                <td class="fw-semibold text-navy">{{ $att->name }}</td>
                                <td class="text-muted">{{ $att->designation }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="attendance[{{ $att->id }}]" id="att_{{ $att->id }}_present" value="attended" {{ $att->pivot->attendance === 'attended' || old("attendance.{$att->id}") === 'attended' ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-success" for="att_{{ $att->id }}_present">Present</label>

                                        <input type="radio" class="btn-check" name="attendance[{{ $att->id }}]" id="att_{{ $att->id }}_absent" value="absent" {{ $att->pivot->attendance === 'absent' || old("attendance.{$att->id}") === 'absent' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger" for="att_{{ $att->id }}_absent">Absent</label>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('nba.meetings.show', $meeting) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-success px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Save MoM & Complete</button>
            </div>
        </form>
    </div>
</div>
@endsection
