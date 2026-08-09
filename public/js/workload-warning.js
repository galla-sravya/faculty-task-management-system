document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.workload-check-form');

    forms.forEach(form => {
        form.addEventListener('submit', async function (e) {
            // If already acknowledged, let it submit normally
            if (form.querySelector('input[name="workload_warning_acknowledged"]')) {
                return;
            }

            // Find assignees (multiple select for create/edit, or single select for reassign)
            let facultyIds = [];
            const assigneesSelect = form.querySelector('select[name="assignees[]"]');
            const collabsSelect = form.querySelector('select[name="collaborators[]"]');
            const newAssigneeSelect = form.querySelector('select[name="new_assignee_id"]'); // For reassign
            
            if (assigneesSelect) {
                Array.from(assigneesSelect.selectedOptions).forEach(option => {
                    if (option.value) facultyIds.push(option.value);
                });
            } else if (collabsSelect) {
                Array.from(collabsSelect.selectedOptions).forEach(option => {
                    if (option.value) facultyIds.push(option.value);
                });
            } else if (newAssigneeSelect && newAssigneeSelect.value) {
                facultyIds.push(newAssigneeSelect.value);
            }

            // Find deadline
            const deadlineInput = form.querySelector('input[name="deadline"]');
            const deadline = deadlineInput ? deadlineInput.value : form.dataset.taskDeadline;
            
            // Find task ID (for edit/reassign exclusion)
            const taskId = form.dataset.taskId;

            // If we have no faculty or no deadline/taskId to check against, proceed normally
            if (facultyIds.length === 0 || (!deadline && !taskId)) {
                return;
            }

            e.preventDefault(); // Stop submission to check workload
            
            // Show some loading state on the submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : null;
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Checking...';
                submitBtn.disabled = true;
            }

            try {
                // Determine base URL since we don't have blade helpers in JS
                // But we can use a relative path if we assume standard routing
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const response = await fetch('/tasks/workload-check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        faculty_ids: facultyIds,
                        deadline: deadline,
                        task_id: taskId
                    })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                
                if (data.overloaded && data.overloaded.length > 0) {
                    // Faculty are overloaded, show confirmation
                    let msgLines = data.overloaded.map(f => `Dr. ${f.name} already has ${f.count} tasks due on this date assigned by you.`);
                    let message = msgLines.join('\n') + '\n\nStill assign this task to them?';
                    
                    if (confirm(message)) {
                        // Acknowledged, proceed
                        addAcknowledgedInputAndSubmit(form);
                    } else {
                        // Cancelled, restore button
                        if (submitBtn && originalBtnHtml) {
                            submitBtn.innerHTML = originalBtnHtml;
                            submitBtn.disabled = false;
                        }
                    }
                } else {
                    // No overload, proceed
                    addAcknowledgedInputAndSubmit(form);
                }
            } catch (error) {
                console.error('Workload check failed:', error);
                // On failure, fallback to submitting normally so we don't break the app
                addAcknowledgedInputAndSubmit(form);
            }
        });
    });

    function addAcknowledgedInputAndSubmit(form) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'workload_warning_acknowledged';
        input.value = '1';
        form.appendChild(input);
        
        // Use HTMLFormElement.prototype.submit to bypass our event listener
        HTMLFormElement.prototype.submit.call(form);
    }
});
