function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function configureAxios() {
    if (!window.axios) {
        return;
    }

    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = getCsrfToken();
    window.axios.defaults.headers.common['Accept'] = 'application/json';
    window.axios.defaults.withCredentials = true;
    window.axios.defaults.withXSRFToken = true;
}

document.addEventListener('alpine:init', () => {
    configureAxios();

    window.Alpine.data('clientHome', () => ({
        loading: !window.__CLIENT_HOME_INITIAL__,
        actionLoading: false,
        error: null,
        data: window.__CLIENT_HOME_INITIAL__ ?? null,

        async init() {
            if (!this.data) {
                await this.refresh();
            }
        },

        async refresh() {
            this.loading = true;
            this.error = null;

            try {
                const response = await window.axios.get('/client/home/data');
                this.data = response.data.data ?? response.data;
            } catch (error) {
                this.error = 'تعذر تحميل بيانات اليوم. حاول مرة أخرى.';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async completeWorkout(scheduleId) {
            await this.workoutAction(scheduleId, 'complete');
        },

        async skipWorkout(scheduleId) {
            await this.workoutAction(scheduleId, 'skip');
        },

        async workoutAction(scheduleId, action) {
            this.actionLoading = true;

            try {
                await window.axios.post(`/client/workouts/${scheduleId}/${action}`);
                await this.refresh();
            } catch (error) {
                this.error = 'تعذر تحديث حالة التمرين.';
                console.error(error);
            } finally {
                this.actionLoading = false;
            }
        },

        async toggleHabit(habit) {
            this.actionLoading = true;

            try {
                const isCompleted = !(habit.today_log?.is_completed);
                await window.axios.post(`/client/habits/${habit.id}/log`, {
                    is_completed: isCompleted,
                    logged_on: this.data?.date,
                });
                await this.refresh();
            } catch (error) {
                this.error = 'تعذر تحديث العادة.';
                console.error(error);
            } finally {
                this.actionLoading = false;
            }
        },
    }));
});

let deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;

    if (document.body.dataset.clientApp !== '1') {
        return;
    }

    const banner = document.getElementById('pwa-install-banner');
    if (banner && !localStorage.getItem('pwa-install-dismissed')) {
        banner.classList.remove('hidden');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const installBtn = document.getElementById('pwa-install-btn');
    const dismissBtn = document.getElementById('pwa-install-dismiss');
    const banner = document.getElementById('pwa-install-banner');

    installBtn?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) {
            return;
        }

        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        banner?.classList.add('hidden');
    });

    dismissBtn?.addEventListener('click', () => {
        localStorage.setItem('pwa-install-dismissed', '1');
        banner?.classList.add('hidden');
    });
});

export {};
