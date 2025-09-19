<template>
    <div>
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="/">TaskOrders</a>
                
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">
                        {{ user.name }} (Руководитель)
                    </span>
                    <button class="btn btn-outline-danger btn-sm" @click="logout">
                        Выйти
                    </button>
                </div>
            </div>
        </nav>
        
        <orders-table></orders-table>
    </div>
</template>

<script>
export default {
    name: 'Dashboard',
    data() {
        return {
            user: null
        }
    },
    mounted() {
        this.getUserData();
    },
    methods: {
        async getUserData() {
            try {
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
                    this.user = data.user;
                } else {
                    window.location.href = '/';
                }
            } catch (error) {
                console.error('Error getting user data:', error);
                window.location.href = '/';
            }
        },
        logout() {
            window.location.href = '/logout';
        }
    }
}
</script>