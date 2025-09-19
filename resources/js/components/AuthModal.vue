<template>
    <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalLabel">Авторизация</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="errorMessage" class="alert alert-danger">
                        {{ errorMessage }}
                    </div>
                    
                    <form @submit.prevent="login">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                v-model="form.email"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                v-model="form.password"
                                required
                            >
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                Войти
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AuthModal',
    data() {
        return {
            form: {
                email: '',
                password: ''
            },
            loading: false,
            errorMessage: ''
        }
    },
    methods: {
        async login() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'include',
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    //данные пользователя
                    const userResponse = await fetch('/api/check-auth', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'include'
                    });
                    
                    const userData = await userResponse.json();
                    
                    this.closeModal();
                    
                    //успех авторизации
                    this.$emit('login-success', userData.user);
                    
                    this.errorMessage = '';
                } else {
                    this.errorMessage = data.message || 'Ошибка авторизации';
                }
            } catch (error) {
                this.errorMessage = 'Ошибка при авторизации';
                console.error('Login error:', error);
            } finally {
                this.loading = false;
            }
        },

        closeModal() {
            const modalElement = document.getElementById('authModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
        }
    }
}
</script>