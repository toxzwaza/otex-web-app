<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
  questionnaire: Object
});

const isEditing = ref(false);
const formData = reactive({
  casting_experience: props.questionnaire.casting_experience || false,
  casting_staff: props.questionnaire.casting_staff || '',
  sand_experience: props.questionnaire.sand_experience || false,
  sand_staff: props.questionnaire.sand_staff || '',
  memo: props.questionnaire.memo || ''
});

// 編集開始
const startEdit = () => {
  isEditing.value = true;
};

// 編集キャンセル
const cancelEdit = () => {
  isEditing.value = false;
  // 元のデータに戻す
  formData.casting_experience = props.questionnaire.casting_experience || false;
  formData.casting_staff = props.questionnaire.casting_staff || '';
  formData.sand_experience = props.questionnaire.sand_experience || false;
  formData.sand_staff = props.questionnaire.sand_staff || '';
  formData.memo = props.questionnaire.memo || '';
};

// データ保存
const saveData = async () => {
  try {
    const response = await axios.put(`/admin/questionnaire/${props.questionnaire.id}`, formData);
    
    if (response.data.status) {
      // 成功時はpropsを更新
      Object.assign(props.questionnaire, formData);
      isEditing.value = false;
      alert('データが正常に更新されました');
    } else {
      alert('更新に失敗しました: ' + response.data.message);
    }
  } catch (error) {
    console.error('Update error:', error);
    alert('更新エラーが発生しました');
  }
};

// 管理画面に戻る
const goBack = () => {
  window.history.back();
};
</script>

<template>
  <div class="detail-page">
    <!-- ヘッダー -->
    <div class="header">
      <div class="header-left">
        <button @click="goBack" class="back-btn">
          ← 管理画面に戻る
        </button>
        <h1>アンケート詳細</h1>
      </div>
      <div class="header-right">
        <button v-if="!isEditing" @click="startEdit" class="edit-btn">
          ✏️ 編集
        </button>
        <div v-else class="edit-actions">
          <button @click="saveData" class="save-btn">💾 保存</button>
          <button @click="cancelEdit" class="cancel-btn">❌ キャンセル</button>
        </div>
      </div>
    </div>

    <!-- 基本情報 -->
    <div class="info-section">
      <h2>基本情報</h2>
      <div class="info-grid">
        <div class="info-item">
          <label>ID</label>
          <div class="info-value">{{ questionnaire.id }}</div>
        </div>
        <div class="info-item">
          <label>UID</label>
          <div class="info-value uid-value">{{ questionnaire.uid }}</div>
        </div>
        <div class="info-item">
          <label>学校</label>
          <div class="info-value">{{ questionnaire.school || '未回答' }}</div>
        </div>
        <div class="info-item">
          <label>学科</label>
          <div class="info-value">{{ questionnaire.department || '未回答' }}</div>
        </div>
        <div class="info-item">
          <label>学年</label>
          <div class="info-value">{{ questionnaire.grade || '未回答' }}</div>
        </div>
        <div class="info-item">
          <label>性別</label>
          <div class="info-value">{{ questionnaire.gender || '未回答' }}</div>
        </div>
        <div class="info-item">
          <label>回答日時</label>
          <div class="info-value">{{ new Date(questionnaire.created_at).toLocaleString('ja-JP') }}</div>
        </div>
        <div class="info-item">
          <label>更新日時</label>
          <div class="info-value">{{ new Date(questionnaire.updated_at).toLocaleString('ja-JP') }}</div>
        </div>
      </div>
    </div>

    <!-- 体験記録 -->
    <div class="experience-section">
      <h2>体験記録</h2>
      
      <!-- 注湯体験 -->
      <div class="experience-card">
        <div class="experience-header">
          <h3>🔥 注湯体験</h3>
          <div class="experience-status">
            <input 
              v-if="isEditing"
              v-model="formData.casting_experience"
              type="checkbox"
              class="edit-checkbox"
            />
            <span v-else class="status-badge" :class="{ active: questionnaire.casting_experience }">
              {{ questionnaire.casting_experience ? '体験済み' : '未体験' }}
            </span>
          </div>
        </div>
        <div class="experience-content">
          <div class="field-group">
            <label>対応者</label>
            <input 
              v-if="isEditing"
              v-model="formData.casting_staff"
              type="text"
              class="edit-input"
              placeholder="対応者名を入力してください"
            />
            <div v-else class="field-value">
              {{ questionnaire.casting_staff || '未入力' }}
            </div>
          </div>
        </div>
      </div>

      <!-- 砂込め体験 -->
      <div class="experience-card">
        <div class="experience-header">
          <h3>🏖️ 砂込め体験</h3>
          <div class="experience-status">
            <input 
              v-if="isEditing"
              v-model="formData.sand_experience"
              type="checkbox"
              class="edit-checkbox"
            />
            <span v-else class="status-badge" :class="{ active: questionnaire.sand_experience }">
              {{ questionnaire.sand_experience ? '体験済み' : '未体験' }}
            </span>
          </div>
        </div>
        <div class="experience-content">
          <div class="field-group">
            <label>対応者</label>
            <input 
              v-if="isEditing"
              v-model="formData.sand_staff"
              type="text"
              class="edit-input"
              placeholder="対応者名を入力してください"
            />
            <div v-else class="field-value">
              {{ questionnaire.sand_staff || '未入力' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- メモ -->
    <div class="memo-section">
      <h2>メモ</h2>
      <div class="memo-card">
        <textarea 
          v-if="isEditing"
          v-model="formData.memo"
          class="memo-textarea"
          placeholder="メモを入力してください"
          rows="6"
        ></textarea>
        <div v-else class="memo-content">
          <pre v-if="questionnaire.memo">{{ questionnaire.memo }}</pre>
          <div v-else class="no-memo">メモはありません</div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.detail-page {
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

  .header-left {
    display: flex;
    align-items: center;
    gap: 16px;

    h1 {
      color: #1f2937;
      font-size: 1.8rem;
      font-weight: 700;
      margin: 0;
    }
  }

  .back-btn {
    background: #6b7280;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;

    &:hover {
      background: #4b5563;
    }
  }

  .edit-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
  }

  .edit-actions {
    display: flex;
    gap: 12px;

    .save-btn {
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

    .cancel-btn {
      background: #6b7280;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;

      &:hover {
        background: #4b5563;
      }
    }
  }
}

.info-section, .experience-section, .memo-section {
  margin-bottom: 30px;

  h2 {
    color: #1f2937;
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 16px;
  }
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.info-item {
  label {
    display: block;
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 4px;
  }

  .info-value {
    color: #1f2937;
    font-weight: 600;
    padding: 8px 0;
    border-bottom: 1px solid #f3f4f6;

    &.uid-value {
      font-family: monospace;
      background: #f3f4f6;
      padding: 8px 12px;
      border-radius: 6px;
      border: none;
    }
  }
}

.experience-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 16px;
  overflow: hidden;

  .experience-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;

    h3 {
      color: #1f2937;
      font-size: 1.2rem;
      font-weight: 600;
      margin: 0;
    }
  }

  .experience-content {
    padding: 24px;
  }
}

.field-group {
  label {
    display: block;
    color: #374151;
    font-weight: 500;
    margin-bottom: 8px;
  }

  .field-value {
    color: #1f2937;
    padding: 12px 16px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
  }
}

.status-badge {
  display: inline-block;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  background: #ef4444;
  color: white;

  &.active {
    background: #10b981;
  }
}

.edit-input, .edit-checkbox {
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  width: 100%;
  transition: border-color 0.3s ease;

  &:focus {
    outline: none;
    border-color: #3b82f6;
  }
}

.edit-checkbox {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.memo-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 24px;

  .memo-textarea {
    width: 100%;
    padding: 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    resize: vertical;
    transition: border-color 0.3s ease;

    &:focus {
      outline: none;
      border-color: #3b82f6;
    }
  }

  .memo-content {
    pre {
      white-space: pre-wrap;
      word-wrap: break-word;
      color: #1f2937;
      line-height: 1.6;
      margin: 0;
    }

    .no-memo {
      color: #6b7280;
      font-style: italic;
      text-align: center;
      padding: 40px;
    }
  }
}

@media (max-width: 768px) {
  .detail-page {
    padding: 10px;
  }

  .header {
    flex-direction: column;
    gap: 16px;
    text-align: center;

    .header-left {
      flex-direction: column;
      gap: 12px;

      h1 {
        font-size: 1.5rem;
      }
    }

    .edit-actions {
      justify-content: center;
    }
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .experience-header {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }
}
</style>
