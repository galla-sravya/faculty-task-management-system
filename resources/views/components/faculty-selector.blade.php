@php
    $selected = $selected ?? [];
    $inputName = $inputName ?? 'assignees[]';
@endphp

<div class="faculty-selector-container">
    <div class="mb-2">
        <input type="text" class="form-control form-control-sm border faculty-search-input" style="border-color: var(--border) !important;" placeholder="Search faculty by name..." autocomplete="off">
    </div>
    
    <div class="border rounded px-3 py-2 overflow-y-auto" style="max-height: 200px; border-color: var(--border) !important; background-color: #fff;">
        <div class="form-check border-bottom pb-2 mb-2 faculty-select-all-container">
            <input class="form-check-input faculty-select-all" type="checkbox" id="selectAllFaculty">
            <label class="form-check-label fw-semibold text-dark w-100" for="selectAllFaculty" style="cursor: pointer;">
                Select All Faculty
            </label>
        </div>

        <div class="faculty-list">
            @php
                $oldKey = str_replace('[]', '', $inputName);
            @endphp
            @foreach($faculties as $faculty)
                <div class="form-check faculty-item mb-2">
                    <input class="form-check-input faculty-checkbox" type="checkbox" name="{{ $inputName }}" value="{{ $faculty->id }}" id="faculty_{{ $faculty->id }}" {{ in_array($faculty->id, old($oldKey, $selected)) ? 'checked' : '' }}>
                    <label class="form-check-label w-100 text-secondary" for="faculty_{{ $faculty->id }}" style="cursor: pointer;">
                        {{ $faculty->name }} <span class="small text-muted">({{ $faculty->designation }})</span>
                    </label>
                </div>
            @endforeach
            <div class="faculty-no-results text-muted small d-none py-2">No faculty found matching your search.</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectors = document.querySelectorAll('.faculty-selector-container');
        
        selectors.forEach(container => {
            const searchInput = container.querySelector('.faculty-search-input');
            const selectAllCheckbox = container.querySelector('.faculty-select-all');
            const facultyCheckboxes = container.querySelectorAll('.faculty-checkbox');
            const facultyItems = container.querySelectorAll('.faculty-item');
            const noResultsMsg = container.querySelector('.faculty-no-results');

            const updateSelectAllState = () => {
                const totalVisible = Array.from(facultyItems).filter(item => !item.classList.contains('d-none')).length;
                const totalVisibleChecked = Array.from(facultyCheckboxes).filter(cb => cb.checked && !cb.closest('.faculty-item').classList.contains('d-none')).length;
                
                if (totalVisible === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.disabled = true;
                } else {
                    selectAllCheckbox.disabled = false;
                    if (totalVisibleChecked === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else if (totalVisibleChecked === totalVisible) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                }
            };

            facultyCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectAllState);
            });

            selectAllCheckbox.addEventListener('change', function () {
                const isChecked = this.checked;
                facultyCheckboxes.forEach(cb => {
                    const item = cb.closest('.faculty-item');
                    if (!item.classList.contains('d-none')) {
                        cb.checked = isChecked;
                    }
                });
                updateSelectAllState();
            });

            searchInput.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                let visibleCount = 0;

                facultyItems.forEach(item => {
                    const label = item.querySelector('.form-check-label').textContent.toLowerCase();
                    if (label.includes(term)) {
                        item.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        item.classList.add('d-none');
                    }
                });

                if (visibleCount === 0) {
                    noResultsMsg.classList.remove('d-none');
                } else {
                    noResultsMsg.classList.add('d-none');
                }
                
                updateSelectAllState();
            });

            updateSelectAllState();
        });
    });
</script>

