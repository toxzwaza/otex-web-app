<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  questionnaires: Array,
  stats: Object
});

const searchTerm = ref('');
const selectedSchool = ref('');
const selectedGrade = ref('');
const selectedGender = ref('');

// フィルタリング機能
const filteredQuestionnaires = computed(() => {
  return props.questionnaires.filter(item => {
    const matchesSearch = !searchTerm.value || 
      item.school.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      item.department.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      item.uid.toLowerCase().includes(searchTerm.value.toLowerCase());
    
    const matchesSchool = !selectedSchool.value || item.school === selectedSchool.value;
    const matchesGrade = !selectedGrade.value || item.grade === selectedGrade.value;
    const matchesGender = !selectedGender.value || item.gender === selectedGender.value;
    
    return matchesSearch && matchesSchool && matchesGrade && matchesGender;
  });
});

// ユニークな値を取得
const uniqueSchools = computed(() => {
  return [...new Set(props.questionnaires.map(item => item.school))];
});

const uniqueGrades = computed(() => {
  return [...new Set(props.questionnaires.map(item => item.grade))];
});

const uniqueGenders = computed(() => {
  return [...new Set(props.questionnaires.map(item => item.gender))];
});

// CSVエクスポート
const exportData = () => {
  window.location.href = '/admin/export';
};

// フィルターリセット
const resetFilters = () => {
  searchTerm.value = '';
  selectedSchool.value = '';
  selectedGrade.value = '';
  selectedGender.value = '';
};
</script>

<template>
  <div class="admin-dashboard">
    <!-- ヘッダー -->
    <div class="header">
      <h1>アンケート管理画面</h1>
      <button @click="exportData" class="export-btn">
        📊 CSVエクスポート
      </button>
    </div>

    <!-- 統計情報 -->
    <div class="stats-grid">
      <div class="stat-card">
        <h3>総回答数</h3>
        <div class="stat-number">{{ stats.total }}</div>
      </div>
      <div class="stat-card">
        <h3>学校数</h3>
        <div class="stat-number">{{ Object.keys(stats.schools).length }}</div>
      </div>
      <div class="stat-card">
        <h3>学科数</h3>
        <div class="stat-number">{{ Object.keys(stats.departments).length }}</div>
      </div>
      <div class="stat-card">
        <h3>最新回答</h3>
        <div class="stat-text">
          {{ questionnaires.length > 0 ? new Date(questionnaires[0].created_at).toLocaleDateString('ja-JP') : 'なし' }}
        </div>
      </div>
    </div>

    <!-- フィルター -->
    <div class="filters">
      <div class="filter-row">
        <input
          v-model="searchTerm"
          type="text"
          placeholder="検索（学校名、学科名、UID）"
          class="search-input"
        />
        <select v-model="selectedSchool" class="filter-select">
          <option value="">すべての学校</option>
          <option v-for="school in uniqueSchools" :key="school" :value="school">
            {{ school }}
          </option>
        </select>
        <select v-model="selectedGrade" class="filter-select">
          <option value="">すべての学年</option>
          <option v-for="grade in uniqueGrades" :key="grade" :value="grade">
            {{ grade }}
          </option>
        </select>
        <select v-model="selectedGender" class="filter-select">
          <option value="">すべての性別</option>
          <option v-for="gender in uniqueGenders" :key="gender" :value="gender">
            {{ gender }}
          </option>
        </select>
        <button @click="resetFilters" class="reset-btn">リセット</button>
      </div>
      <div class="filter-info">
        {{ filteredQuestionnaires.length }} / {{ questionnaires.length }} 件表示
      </div>
    </div>

    <!-- データテーブル -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>UID</th>
            <th>学校</th>
            <th>学科</th>
            <th>学年</th>
            <th>性別</th>
            <th>回答日時</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredQuestionnaires" :key="item.id">
            <td>{{ item.id }}</td>
            <td class="uid-cell">{{ item.uid }}</td>
            <td>{{ item.school }}</td>
            <td>{{ item.department }}</td>
            <td>{{ item.grade }}</td>
            <td>{{ item.gender }}</td>
            <td>{{ new Date(item.created_at).toLocaleString('ja-JP') }}</td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="filteredQuestionnaires.length === 0" class="no-data">
        データがありません
      </div>
    </div>

    <!-- 統計チャート -->
    <div class="charts-grid">
      <div class="chart-card">
        <h3>学校別回答数</h3>
        <div class="chart-list">
          <div v-for="(count, school) in stats.schools" :key="school" class="chart-item">
            <span class="chart-label">{{ school }}</span>
            <div class="chart-bar">
              <div 
                class="chart-fill" 
                :style="{ width: (count / stats.total * 100) + '%' }"
              ></div>
            </div>
            <span class="chart-count">{{ count }}</span>
          </div>
        </div>
      </div>

      <div class="chart-card">
        <h3>学年別回答数</h3>
        <div class="chart-list">
          <div v-for="(count, grade) in stats.grades" :key="grade" class="chart-item">
            <span class="chart-label">{{ grade }}</span>
            <div class="chart-bar">
              <div 
                class="chart-fill" 
                :style="{ width: (count / stats.total * 100) + '%' }"
              ></div>
            </div>
            <span class="chart-count">{{ count }}</span>
          </div>
        </div>
      </div>

      <div class="chart-card">
        <h3>性別回答数</h3>
        <div class="chart-list">
          <div v-for="(count, gender) in stats.genders" :key="gender" class="chart-item">
            <span class="chart-label">{{ gender }}</span>
            <div class="chart-bar">
              <div 
                class="chart-fill" 
                :style="{ width: (count / stats.total * 100) + '%' }"
              ></div>
            </div>
            <span class="chart-count">{{ count }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.admin-dashboard {
  min-height: 100vh;
  background: #f8fafc;
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

  h1 {
    color: #1f2937;
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
  }

  .export-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;

  h3 {
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 500;
    margin: 0 0 12px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .stat-number {
    color: #1f2937;
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
  }

  .stat-text {
    color: #1f2937;
    font-size: 1.1rem;
    font-weight: 600;
  }
}

.filters {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;

  .filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 12px;
    margin-bottom: 12px;

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }
  }

  .search-input, .filter-select {
    padding: 10px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: border-color 0.3s ease;

    &:focus {
      outline: none;
      border-color: #3b82f6;
    }
  }

  .reset-btn {
    background: #6b7280;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;

    &:hover {
      background: #4b5563;
    }
  }

  .filter-info {
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 500;
  }
}

.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 30px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;

  th {
    background: #f9fafb;
    color: #374151;
    font-weight: 600;
    padding: 16px 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    font-size: 0.9rem;
  }

  td {
    padding: 12px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.9rem;
    color: #1f2937;
  }

  .uid-cell {
    font-family: monospace;
    font-size: 0.8rem;
    background: #f3f4f6;
  }

  tr:hover {
    background: #f9fafb;
  }
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #6b7280;
  font-size: 1.1rem;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.chart-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

  h3 {
    color: #1f2937;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 20px 0;
  }
}

.chart-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.chart-item {
  display: grid;
  grid-template-columns: 1fr 2fr auto;
  align-items: center;
  gap: 12px;

  .chart-label {
    font-size: 0.9rem;
    color: #374151;
    font-weight: 500;
  }

  .chart-bar {
    background: #e5e7eb;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;

    .chart-fill {
      background: linear-gradient(90deg, #3b82f6, #1d4ed8);
      height: 100%;
      transition: width 0.6s ease;
    }
  }

  .chart-count {
    font-size: 0.9rem;
    color: #1f2937;
    font-weight: 600;
    min-width: 30px;
    text-align: right;
  }
}

@media (max-width: 768px) {
  .admin-dashboard {
    padding: 10px;
  }

  .header {
    flex-direction: column;
    gap: 16px;
    text-align: center;

    h1 {
      font-size: 1.5rem;
    }
  }

  .data-table {
    font-size: 0.8rem;

    th, td {
      padding: 8px 6px;
    }
  }
}
</style>
