<template>
    <div>
        <!-- Навбар -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light" v-if="isAuthenticated">
            <div class="container">
                <a class="navbar-brand" href="#">TaskOrders</a>
                
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">
                        {{ user.name }} ({{ userRole }})
                    </span>
                    <button class="btn btn-outline-danger btn-sm" @click="logout">
                        Выйти
                    </button>
                </div>
            </div>
        </nav>

        <!-- Основной контент -->
        <div class="container mt-4">
            <div v-if="!isAuthenticated">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <h3>Система управления заказами</h3>
                        <p>Для работы с системой необходимо авторизоваться</p>
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#authModal">
                            Войти в систему
                        </button>
                    </div>
                </div>

            </div>

            <div v-else>
                <div class="card">
                    <div class="card-header">
                        <h4>Добро пожаловать, {{ user.name }}!</h4>
                    </div>
                    <div class="card-body">
                        <p>Ваша роль: <strong>{{ userRole }}</strong></p>
                        
                        <div v-if="user.role === 'manager'">
                            <h5>Панель руководителя</h5>
                            <p>Доступные действия: просмотр всех заказов и создание заказов</p>
                            <a href="/dashboard" class="btn btn-primary me-2">Перейти к заказам</a>
                        </div>
                        
                        <div v-else>
                            <h5>Панель оператора</h5>
                            <p>Доступные действия: создание заказов</p>
                            <a href="/orders/create" class="btn btn-success">Создать заказ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно авторизации -->
        <auth-modal @login-success="handleLoginSuccess"></auth-modal>
    </div>
</template>

<script>
export default {
    name: 'App',
    data() {
        return {
            isAuthenticated: false,
            user: null,
            loading: false,
            testResult: null
        }
    },
    computed: {
        userRole() {
            if (!this.user) return '';
            return this.user.role === 'manager' ? 'Руководитель' : 'Оператор';
        }
    },
    mounted() {
        this.checkAuth();
    },
    methods: {
        async checkAuth() {
            try {
                this.loading = true;
                
                // Простой fetch запрос
                const response = await fetch('/api/check-auth', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.authenticated) {
                    this.isAuthenticated = true;
                    this.user = data.user;
                } else {
                    this.isAuthenticated = false;
                    this.user = null;
                }
            } catch (error) {
                console.error('Auth check error:', error);
                this.isAuthenticated = false;
                this.user = null;
            } finally {
                this.loading = false;
            }
        },

        async testSimpleRequest() {
            try {
                const response = await fetch('/api/check-auth');
                const data = await response.json();
                this.testResult = data;
                console.log('Simple test result:', data);
            } catch (error) {
                console.error('Simple test error:', error);
                this.testResult = { error: error.message };
            }
        },

        handleLoginSuccess(userData) {
            this.isAuthenticated = true;
            this.user = userData;
            
            setTimeout(() => {
                if (userData.role === 'manager') {
                    window.location.href = '/dashboard';
                } else {
                    window.location.href = '/orders/create';
                }
            }, 1000);
        },

        // async logout() {
        //     try {
        //         // Простой fetch для logout
        //         await fetch('/logout', {
        //             method: 'POST',
        //             headers: {
        //                 'Accept': 'application/json',
        //                 'Content-Type': 'application/json',
        //                 'X-Requested-With': 'XMLHttpRequest',
        //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        //             },
        //             credentials: 'include'
        //         });
                
        //         this.isAuthenticated = false;
        //         this.user = null;
        //         window.location.reload();
        //     } catch (error) {
        //         console.error('Logout error:', error);
        //     }
        // }
        async logout() {
            try {
                // Простой редирект вместо fetch запроса
                window.location.href = '/logout';
            } catch (error) {
                console.error('Logout error:', error);
            }
        }
    }
}
</script>
