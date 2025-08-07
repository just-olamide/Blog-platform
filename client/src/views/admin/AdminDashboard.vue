<template>
  <div class="admin-dashboard">
    <!-- Header -->
    <div class="dashboard-header bg-primary text-white py-4 mb-4">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h1 class="h3 mb-0">
              <i class="bi bi-speedometer2 me-2"></i>
              Admin Dashboard
            </h1>
            <p class="mb-0 opacity-75">Manage your blog platform</p>
          </div>
          <div class="col-md-6 text-md-end">
            <button class="btn btn-light btn-sm me-2" @click="exportData">
              <i class="bi bi-download me-1"></i>Export Data
            </button>
            <button class="btn btn-outline-light btn-sm" @click="printDashboard">
              <i class="bi bi-printer me-1"></i>Print
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading dashboard...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ error }}
      </div>

      <!-- Dashboard Content -->
      <div v-else>
        <!-- Overview Cards -->
        <div class="row mb-4">
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h5 class="card-title mb-1">Total Posts</h5>
                    <h2 class="mb-0">{{ stats.totalPosts }}</h2>
                  </div>
                  <div class="ms-3">
                    <i class="bi bi-journal-text fs-1 opacity-75"></i>
                  </div>
                </div>
                <div class="mt-2">
                  <small class="opacity-75">
                    <i class="bi bi-arrow-up me-1"></i>
                    +{{ stats.newPostsThisMonth }} this month
                  </small>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h5 class="card-title mb-1">Total Users</h5>
                    <h2 class="mb-0">{{ stats.totalUsers }}</h2>
                  </div>
                  <div class="ms-3">
                    <i class="bi bi-people fs-1 opacity-75"></i>
                  </div>
                </div>
                <div class="mt-2">
                  <small class="opacity-75">
                    <i class="bi bi-arrow-up me-1"></i>
                    +{{ stats.newUsersThisMonth }} this month
                  </small>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h5 class="card-title mb-1">Total Comments</h5>
                    <h2 class="mb-0">{{ stats.totalComments }}</h2>
                  </div>
                  <div class="ms-3">
                    <i class="bi bi-chat-dots fs-1 opacity-75"></i>
                  </div>
                </div>
                <div class="mt-2">
                  <small class="opacity-75">
                    <i class="bi bi-arrow-up me-1"></i>
                    +{{ stats.newCommentsThisMonth }} this month
                  </small>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h5 class="card-title mb-1">Total Likes</h5>
                    <h2 class="mb-0">{{ stats.totalLikes }}</h2>
                  </div>
                  <div class="ms-3">
                    <i class="bi bi-heart fs-1 opacity-75"></i>
                  </div>
                </div>
                <div class="mt-2">
                  <small class="opacity-75">
                    <i class="bi bi-arrow-up me-1"></i>
                    +{{ stats.newLikesThisMonth }} this month
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
          <!-- Posts Over Time Chart -->
          <div class="col-lg-8 mb-4">
            <div class="card h-100">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="bi bi-bar-chart me-2"></i>
                  Posts Over Time
                </h5>
              </div>
              <div class="card-body">
                <canvas ref="postsChart" width="400" height="200"></canvas>
              </div>
            </div>
          </div>

          <!-- User Activity Chart -->
          <div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="bi bi-pie-chart me-2"></i>
                  User Activity
                </h5>
              </div>
              <div class="card-body">
                <canvas ref="activityChart" width="300" height="300"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity & Quick Actions -->
        <div class="row">
          <!-- Recent Posts -->
          <div class="col-lg-6 mb-4">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                  <i class="bi bi-clock-history me-2"></i>
                  Recent Posts
                </h5>
                <router-link to="/admin/posts" class="btn btn-outline-primary btn-sm">
                  View All
                </router-link>
              </div>
              <div class="card-body">
                <div v-if="recentPosts.length === 0" class="text-center text-muted py-3">
                  No recent posts
                </div>
                <div v-else>
                  <div 
                    v-for="post in recentPosts" 
                    :key="post.id"
                    class="d-flex align-items-center py-2 border-bottom"
                  >
                    <div class="flex-grow-1">
                      <h6 class="mb-1">{{ post.title }}</h6>
                      <small class="text-muted">
                        by {{ post.user.name }} • {{ formatDate(post.created_at) }}
                      </small>
                    </div>
                    <div class="dropdown">
                      <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        Actions
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <router-link :to="`/posts/${post.id}`" class="dropdown-item">
                            <i class="bi bi-eye me-2"></i>View
                          </router-link>
                        </li>
                        <li>
                          <button class="dropdown-item" @click="moderatePost(post.id, 'edit')">
                            <i class="bi bi-pencil me-2"></i>Edit
                          </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <button class="dropdown-item text-danger" @click="moderatePost(post.id, 'delete')">
                            <i class="bi bi-trash me-2"></i>Delete
                          </button>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="col-lg-6 mb-4">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="bi bi-lightning me-2"></i>
                  Quick Actions
                </h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <router-link to="/admin/posts" class="btn btn-outline-primary w-100">
                      <i class="bi bi-journal-text me-2"></i>
                      Manage Posts
                    </router-link>
                  </div>
                  <div class="col-md-6 mb-3">
                    <router-link to="/admin/users" class="btn btn-outline-success w-100">
                      <i class="bi bi-people me-2"></i>
                      Manage Users
                    </router-link>
                  </div>
                  <div class="col-md-6 mb-3">
                    <router-link to="/admin/comments" class="btn btn-outline-info w-100">
                      <i class="bi bi-chat-dots me-2"></i>
                      Manage Comments
                    </router-link>
                  </div>
                  <div class="col-md-6 mb-3">
                    <router-link to="/admin/logs" class="btn btn-outline-warning w-100">
                      <i class="bi bi-list-ul me-2"></i>
                      Activity Logs
                    </router-link>
                  </div>
                </div>

                <!-- System Status -->
                <div class="mt-4">
                  <h6 class="mb-3">System Status</h6>
                  <div class="row">
                    <div class="col-6">
                      <div class="d-flex align-items-center mb-2">
                        <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                        <small>Database</small>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="d-flex align-items-center mb-2">
                        <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                        <small>API Server</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Chart from 'chart.js/auto'

export default {
  name: 'AdminDashboard',
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()
    
    const loading = ref(true)
    const error = ref(null)
    const stats = ref({
      totalPosts: 0,
      totalUsers: 0,
      totalComments: 0,
      totalLikes: 0,
      newPostsThisMonth: 0,
      newUsersThisMonth: 0,
      newCommentsThisMonth: 0,
      newLikesThisMonth: 0
    })
    const recentPosts = ref([])
    const chartData = ref({
      postsOverTime: [],
      userActivity: []
    })
    
    const postsChart = ref(null)
    const activityChart = ref(null)
    let postsChartInstance = null
    let activityChartInstance = null

    // Check admin access
    if (!authStore.isAdmin) {
      router.push('/')
      return
    }

    const fetchDashboardData = async () => {
      try {
        loading.value = true
        error.value = null

        // Fetch dashboard statistics
        const [statsResponse, postsResponse, chartResponse] = await Promise.all([
          axios.get('/admin/stats'),
          axios.get('/admin/recent-posts'),
          axios.get('/admin/chart-data')
        ])

        stats.value = statsResponse.data
        recentPosts.value = postsResponse.data.data
        chartData.value = chartResponse.data

      } catch (err) {
        console.error('Error fetching dashboard data:', err)
        error.value = err.response?.data?.message || 'Failed to load dashboard data'
      } finally {
        loading.value = false
      }
    }

    const initializeCharts = async () => {
      await nextTick()
      
      // Posts Over Time Chart
      if (postsChart.value) {
        postsChartInstance = new Chart(postsChart.value, {
          type: 'line',
          data: {
            labels: chartData.value.postsOverTime.map(item => item.date),
            datasets: [{
              label: 'Posts Created',
              data: chartData.value.postsOverTime.map(item => item.count),
              borderColor: 'rgb(54, 162, 235)',
              backgroundColor: 'rgba(54, 162, 235, 0.1)',
              tension: 0.1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        })
      }

      // User Activity Chart
      if (activityChart.value) {
        activityChartInstance = new Chart(activityChart.value, {
          type: 'doughnut',
          data: {
            labels: ['Posts', 'Comments', 'Likes', 'Saves'],
            datasets: [{
              data: [
                stats.value.totalPosts,
                stats.value.totalComments,
                stats.value.totalLikes,
                chartData.value.totalSaves || 0
              ],
              backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(255, 205, 86, 0.8)'
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          }
        })
      }
    }

    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const moderatePost = async (postId, action) => {
      if (action === 'delete') {
        if (!confirm('Are you sure you want to delete this post?')) return
        
        try {
          await axios.delete(`/admin/posts/${postId}`)
          // Refresh data
          await fetchDashboardData()
        } catch (err) {
          console.error('Error deleting post:', err)
          alert('Failed to delete post')
        }
      } else if (action === 'edit') {
        router.push(`/posts/${postId}/edit`)
      }
    }

    const exportData = async () => {
      try {
        const response = await axios.get('/admin/export', {
          responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `dashboard-data-${new Date().toISOString().split('T')[0]}.csv`)
        document.body.appendChild(link)
        link.click()
        link.remove()
      } catch (err) {
        console.error('Error exporting data:', err)
        alert('Failed to export data')
      }
    }

    const printDashboard = () => {
      window.print()
    }

    onMounted(async () => {
      await fetchDashboardData()
      await initializeCharts()
    })

    return {
      loading,
      error,
      stats,
      recentPosts,
      postsChart,
      activityChart,
      formatDate,
      moderatePost,
      exportData,
      printDashboard
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.dashboard-header {
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
  background-color: rgba(0, 0, 0, 0.03);
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

@media print {
  .dashboard-header button {
    display: none;
  }
  
  .dropdown {
    display: none;
  }
}
</style>
