// Define the toast function globally or make it available to Alpine
window.toast = function() {
    return {
        groupedToasts: {},
        defaultPosition: 'top',

        init(defaultPosition) {
            this.defaultPosition = defaultPosition;
            this.groupedToasts = {
                'top-right': [],
                'top-left': [],
                'bottom-right': [],
                'bottom-left': [],
                'top': [],
                'bottom': []
            };

            // Optional: Touch events for swipe-to-dismiss
            // Note: Accuracy depends on unique IDs or simple scenarios
            let startX = 0;
            let swipedToastElement = null;

            document.addEventListener('touchstart', e => {
                const toastEl = e.target.closest('.pointer-events-auto[x-show]');
                if (toastEl) {
                    startX = e.changedTouches[0].screenX;
                    swipedToastElement = toastEl; // Remember which element started the swipe
                } else {
                    startX = null;
                    swipedToastElement = null;
                }
            }, { passive: true });

            document.addEventListener('touchend', e => {
                if (startX === null || !swipedToastElement) return;

                const endX = e.changedTouches[0].screenX;
                const deltaX = endX - startX;

                if (Math.abs(deltaX) > 50) { // Swipe threshold
                    // Find the position and index of the swiped toast data
                    // This is still a bit fragile without unique IDs linking DOM to data.
                    // It assumes the DOM order roughly matches the array order within a position group.
                    for (const position in this.groupedToasts) {
                        const group = this.groupedToasts[position];
                        let foundIndex = -1;

                        // Attempt to find the index based on the element reference
                        // This requires iterating through the DOM elements managed by this position's template
                        // Or making assumptions. Let's stick to a simpler assumption for now:
                        // Find the first visible toast in the group related to the swiped element's container
                        const container = swipedToastElement.closest('.fixed[class*="'+position+'"]');
                        if (container) {
                            foundIndex = group.findIndex(toast => toast.visible); // Find first visible in this group
                        }

                        if (foundIndex !== -1) {
                            this.remove(position, foundIndex);
                            break; // Assume only one swipe action processed
                        }
                    }
                }

                // Reset swipe state
                startX = null;
                swipedToastElement = null;
            });
        },

        show(data) {
            // data is dispatched as [ { ...toastData } ] from Livewire 3
            const toastData = data[0] || {};
            const validPositions = ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top', 'bottom'];
            const position = validPositions.includes(toastData.position) ? toastData.position : this.defaultPosition;

            // Determine icon visibility: Use provided value, else default based on variant
            let showIcon = toastData.icon;
            if (typeof showIcon !== 'boolean') {
                showIcon = ['info', 'success', 'warning', 'danger'].includes(toastData.variant);
            }

            const toast = {
                visible: true, // Start visible
                variant: toastData.variant || 'default',
                heading: toastData.heading || null,
                text: toastData.text || '',
                icon: showIcon || false,
                // Add a unique ID for potentially more robust swipe removal later?
                id: Date.now() + Math.random()
            };

            if (!this.groupedToasts[position]) {
                this.groupedToasts[position] = [];
            }

            this.groupedToasts[position].push(toast);

            const duration = typeof toastData.duration === 'number' ? toastData.duration : 4000; // Default 4s
            if (duration > 0) {
                setTimeout(() => {
                    // Find the toast again in case others were added/removed
                    const index = this.groupedToasts[position].indexOf(toast);
                    if (index !== -1) {
                        this.remove(position, index);
                    }
                }, duration);
            }
        },

        remove(position, index) {
            // Check if the position and index are valid
            if (!this.groupedToasts[position] || !this.groupedToasts[position][index]) {
                return; // Toast might already be removed (e.g., rapid clicks/swipes)
            }

            // Set visibility to false to trigger leave transition
            this.groupedToasts[position][index].visible = false;

            // Use a timeout to allow the leave transition to complete before removing from array
            setTimeout(() => {
                // Check again in case things changed during the timeout
                // Find the specific toast object to remove, checking visibility flag
                const toastToRemove = this.groupedToasts[position].find(t => !t.visible);
                if(toastToRemove) {
                    const actualIndex = this.groupedToasts[position].indexOf(toastToRemove);
                    if (actualIndex > -1) {
                        this.groupedToasts[position].splice(actualIndex, 1);
                    }
                }
            }, 300); // Match the leave transition duration (e.g., duration-200 + buffer)
        }
    };
}

// console.log('Toast function initialized.'); // For debugging
