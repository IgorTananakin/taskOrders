<template>
    <div class="table-container">

        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center">Таблица заказов</h2>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">

                    <div class="col-md-8">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Поиск..." 
                                    v-model="tempFilters.search"
                                    @keyup.enter="applyFilters"
                                >
                            </div>
                            <div class="col-md-2">
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    v-model="tempFilters.dateFrom"
                                >
                            </div>
                            <div class="col-md-2">
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    v-model="tempFilters.dateTo"
                                >
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" v-model="tempFilters.status">
                                    <option value="">Все статусы</option>
                                    <option value="completed">Завершён</option>
                                    <option value="in_progress">В работе</option>
                                    <option value="new">Новый</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" @click="applyFilters" :disabled="loading">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    Применить
                                </button>
                                
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary" @click="resetFilters" :disabled="loading">
                                    Сброс
                                </button>
                            </div>
                            
                        </div>
                    </div>

                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end gap-2">

                            <button class="btn btn-info" @click="showStatistics" :disabled="loading">
                                Статистика
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="alert alert-info text-center">
            <div class="spinner-border spinner-border-sm me-2"></div>
            Загрузка данных...
        </div>

        <div v-if="error" class="alert alert-danger text-center">
            {{ error }}
            <button class="btn btn-sm btn-outline-danger ms-2" @click="fetchOrders">
                Повторить
            </button>
        </div>

        <div class="card" v-if="!loading && !error">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Дата</th>
                                <th>ФИО</th>
                                <th>Телефон</th>
                                <th>ИНН</th>
                                <th>Компания</th>
                                <th>Адрес</th>
                                <th>Товары</th>
                                <th>Статус</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders" :key="order.id">
                                <td>{{ order.order_date }}</td>
                                <td>{{ order.full_name }}</td>
                                <td>{{ order.phone }}</td>
                                <td>{{ order.inn }}</td>
                                <td>{{ order.company_name }}</td>
                                <td>{{ order.address }}</td>
                                <td>
                                    <div v-for="product in order.products" :key="product.id" class="product-item">
                                        {{ product.name }} - {{ product.quantity }} {{ product.unit }}
                                        <span class="text-muted">({{ formatPrice(product.price) }} ₽)</span>
                                    </div>
                                </td>
                                <td>
                                    <span :class="getStatusBadgeClass(order.status)">
                                        {{ getStatusText(order.status) }}
                                    </span>
                                </td>
                                <td class="fw-bold">
                                    {{ formatPrice(calculateOrderTotal(order)) }} ₽
                                </td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td colspan="9" class="text-center text-muted py-4">
                                    Заказы не найдены
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <nav aria-label="Page navigation" class="mt-4" v-if="!loading && !error && orders.length > 0">
            <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                    <a class="page-link" href="#" @click.prevent="prevPage">Назад</a>
                </li>
                <li 
                    v-for="page in pagination.last_page" 
                    :key="page" 
                    class="page-item" 
                    :class="{ active: page === pagination.current_page }"
                >
                    <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
                </li>
                <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                    <a class="page-link" href="#" @click.prevent="nextPage">Вперед</a>
                </li>
            </ul>
        </nav>

        <div class="text-center text-muted mt-2" v-if="!loading && !error && orders.length > 0">
            Показано {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}-{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} из {{ pagination.total }} заказов
        </div>

        <!-- модальное окно статистики -->
        <div class="modal fade" :class="{ 'show': showModal }" tabindex="-1" style="display: block;" v-if="showModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Статистика заказов</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="statistics-container">
                            <div class="stat-item">
                                <strong>Всего заказов:</strong> 
                                <span>{{ statistics.total || 0 }}</span>
                            </div>
                            <div class="stat-item">
                                <strong>Новых заказов:</strong> 
                                <span>{{ statistics.new || 0 }}</span>
                            </div>
                            <div class="stat-item">
                                <strong>В работе:</strong> 
                                <span>{{ statistics.in_progress || 0 }}</span>
                            </div>
                            <div class="stat-item">
                                <strong>Завершены:</strong> 
                                <span>{{ statistics.completed || 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-backdrop fade show" v-if="showModal"></div>
    </div>
</template>

<script>
export default {
    name: 'OrdersTable',
    data() {
        return {
            loading: false,
            error: null,
            orders: [],
            pagination: {
                current_page: 1,
                per_page: 10,
                total: 0,
                last_page: 0
            },
            statistics: {
                total: 0,
                new: 0,
                in_progress: 0,
                completed: 0
            },
            tempFilters: {
                search: '',
                dateFrom: '',
                dateTo: '',
                status: ''
            },
            activeFilters: {
                search: '',
                dateFrom: '',
                dateTo: '',
                status: ''
            },
            showModal: false
        }
    },
    mounted() {
        this.fetchOrders();
        this.fetchStatistics();
    },
    methods: {
        //загрузка заказов
        async fetchOrders() {
            this.loading = true;
            this.error = null;
            
            try {
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    ...this.activeFilters
                });

                Array.from(params.entries()).forEach(([key, value]) => {
                    if (!value) params.delete(key);
                });

                const response = await fetch(`/api/orders?${params}`);
                
                if (!response.ok) {
                    throw new Error(`Ошибка загрузки: ${response.status}`);
                }

                const data = await response.json();
                
                this.orders = data.orders;
                this.pagination = {
                    current_page: data.current_page,
                    per_page: data.per_page,
                    total: data.total,
                    last_page: data.last_page
                };

            } catch (err) {
                this.error = err.message;
                console.error('Ошибка загрузки заказов:', err);
            } finally {
                this.loading = false;
            }
        },

        //загрузка статистики
        async fetchStatistics() {
            try {
                const params = new URLSearchParams(this.activeFilters);
                
                // Убираем пустые параметры
                Array.from(params.entries()).forEach(([key, value]) => {
                    if (!value) params.delete(key);
                });

                const response = await fetch(`/api/orders/statistics?${params}`);
                
                if (response.ok) {
                    const data = await response.json();
                    this.statistics = data;
                }
            } catch (err) {
                console.error('Ошибка загрузки статистики:', err);
            }
        },

        applyFilters() {
            this.activeFilters = { ...this.tempFilters };
            this.pagination.current_page = 1;
            this.fetchOrders();
            this.fetchStatistics();
        },

        resetFilters() {
            this.tempFilters = {
                search: '',
                dateFrom: '',
                dateTo: '',
                status: ''
            };
            this.activeFilters = {
                search: '',
                dateFrom: '',
                dateTo: '',
                status: ''
            };
            this.pagination.current_page = 1;
            this.fetchOrders();
            this.fetchStatistics();
        },

        async loadStatistics() {
            try {
                const params = new URLSearchParams(this.activeFilters);
                
                Array.from(params.entries()).forEach(([key, value]) => {
                    if (!value) params.delete(key);
                });

                const response = await fetch(`/api/orders/statistics?${params}`);
                
                if (response.ok) {
                    const data = await response.json();
                    this.statistics = data;
                } else {
                    console.error('Ошибка загрузки статистики');
                }
            } catch (err) {
                console.error('Ошибка загрузки статистики:', err);
            }
        },

        //показ статистики
        showStatistics() {
            this.loadStatistics();
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        //форматирование цены
        formatPrice(price) {
            return new Intl.NumberFormat('ru-RU').format(price);
        },

        //общая суммы заказа
        calculateOrderTotal(order) {
            return order.products.reduce((total, product) => {
                return total + (product.quantity * product.price);
            }, 0);
        },

        getStatusBadgeClass(status) {
            return {
                'badge': true,
                'bg-success': status === 'completed',
                'bg-warning text-dark': status === 'in_progress',
                'bg-secondary': status === 'new'
            };
        },

        getStatusText(status) {
            const statusMap = {
                'completed': 'Завершён',
                'in_progress': 'В работе',
                'new': 'Новый'
            };
            return statusMap[status] || status;
        },

        goToPage(page) {
            this.pagination.current_page = page;
            this.fetchOrders();
        },

        prevPage() {
            if (this.pagination.current_page > 1) {
                this.pagination.current_page--;
                this.fetchOrders();
            }
        },

        nextPage() {
            if (this.pagination.current_page < this.pagination.last_page) {
                this.pagination.current_page++;
                this.fetchOrders();
            }
        }
    }
}
</script>

<style scoped>
.table-container {
    max-width: 1800px;
    margin: 0 auto;
}
.filter-card {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f8f9fa;
}
.table-striped tbody tr:nth-of-type(even) {
    background-color: #e9ecef;
}
.badge {
    font-size: 0.85em;
    padding: 0.4em 0.6em;
}
.product-item {
    font-size: 0.9em;
    margin-bottom: 4px;
    line-height: 1.3;
}
.product-item:last-child {
    margin-bottom: 0;
}

.modal {
    background-color: rgba(0, 0, 0, 0.5);
}
.modal.show {
    display: block;
}
.modal-backdrop {
    z-index: 1040;
}
.modal {
    z-index: 1050;
}

.statistics-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}
.stat-item:last-child {
    border-bottom: none;
}

@media (max-width: 1200px) {
    .table-container {
        overflow-x: auto;
    }
}
</style>