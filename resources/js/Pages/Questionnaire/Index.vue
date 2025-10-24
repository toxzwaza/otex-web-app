<script setup>
import { ref, onMounted, watch } from "vue";

const props = defineProps({
    uid: String,
    schools: Array
})
const currentStep = ref(1);
const totalSteps = 4;
const showCompletionPopup = ref(false);
const headerVideo = ref(null);
const videoLoaded = ref(false);

// 回答格納用オブジェクト
const form = ref({
  uid: null,
  school: "",
  department: "",
  grade: "",
  gender: "",
});

// 質問内容（リアクティブにするためrefを使用）
const questions = ref([
  { key: "school", text: "学校を教えてください！", type: "select", options: [ ] },
  { key: "department", text: "学科を教えてください！", type: "select", options: [ ] },
  { key: "grade", text: "学年をおしえてください！", type: "select", options: ["3年","2年", "1年"] },
  {
    key: "gender",
    text: "性別をおしえてください！",
    type: "select",
    options: ["男性", "女性", "その他"],
  },
]);

const next = () => {
  if (currentStep.value < totalSteps) {
    // アニメーション効果のためにクラスを追加
    const questionEl = document.querySelector('.question');
    questionEl?.classList.add('fade-out');
    
    setTimeout(() => {
      currentStep.value++;
      questionEl?.classList.remove('fade-out');
    }, 200);
  } else {
    console.log("回答結果:", form.value);
    axios.post(route('questionnaire.store'), form.value)
    .then(res => {
      console.log(res.data)
    })
    .catch(error => {
      console.log(error)
    });

    showCompletionMessage();
  }
};

const showCompletionMessage = () => {
  showCompletionPopup.value = true;


};

const closeCompletionPopup = () => {
  // ポップアップを閉じるアニメーション後にリダイレクト
  showCompletionPopup.value = false;
  
  // 少し遅延を入れてスムーズにリダイレクト
  setTimeout(() => {
    // アキオカのWebサイトへリダイレクト
    window.location.href = 'https://akioka1966.co.jp/';
  },500);
};

// 動画関連の処理
const onVideoLoaded = () => {
  videoLoaded.value = true;
  console.log('Background video loaded successfully');
};

const onVideoError = (error) => {
  console.warn('Background video failed to load:', error);
  // 動画が読み込めない場合はフォールバック背景を使用
  videoLoaded.value = false;
};

const back = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

// 現在の質問の回答が入力されているかをチェック
const isCurrentQuestionAnswered = () => {
  const currentQuestion = questions.value[currentStep.value - 1];
  const answer = form.value[currentQuestion.key];
  return answer && answer.trim() !== "";
};

// プログレスバーの色を段階的に濃くする
const getProgressColor = () => {
  const progress = currentStep.value / totalSteps;
  if (progress <= 0.33) {
    return '#87CEEB'; // 薄い青
  } else if (progress <= 0.66) {
    return '#5CC6F5'; // 中間の青
  } else {
    return '#1E90FF'; // 濃い青
  }
};

// 学校選択時に学科選択肢を更新する関数
const updateDepartmentOptions = (selectedSchoolName) => {
    const departmentQuestion = questions.value.find(q => q.key === "department");
    if (departmentQuestion && props.schools) {
        const selectedSchool = props.schools.find(school => school.name === selectedSchoolName);
        if (selectedSchool && selectedSchool.departments) {
            departmentQuestion.options = selectedSchool.departments.map(dept => dept.name);
            // 学校が変更された場合は学科選択をリセット
            form.value.department = "";
            console.log('Department options updated:', departmentQuestion.options);
        } else {
            departmentQuestion.options = [];
        }
    }
};

// フォームの値を監視して学科選択肢を更新
watch(() => form.value.school, (newSchool) => {
    if (newSchool) {
        updateDepartmentOptions(newSchool);
    }
});

onMounted( () => {
    console.log(props.uid, props.schools)
    form.value.uid = props.uid
    
    // schoolのoptionsにprops.schools.nameを格納
    const schoolQuestion = questions.value.find(q => q.key === "school");
    if (schoolQuestion && props.schools) {
        schoolQuestion.options = props.schools.map(school => school.name);
        console.log('School options updated:', schoolQuestion.options)
    }
})
</script>

<template>
  <div class="survey" :class="{ 'video-loaded': videoLoaded }">
    <!-- 背景オーバーレイ -->
    <div class="background-overlay"></div>

    <!-- ヘッダー -->
    <div class="header">
      <!-- ヘッダー動画 -->
      <video 
        ref="headerVideo"
        class="header-video"
        autoplay 
        muted 
        loop 
        playsinline
        preload="metadata"
        @loadeddata="onVideoLoaded"
        @error="onVideoError"
      >
        <source src="/back_movie.mp4" type="video/mp4">
      </video>
      
      <!-- ヘッダーオーバーレイ -->
      <div class="header-overlay"></div>
    </div>

    <!-- マーキーテキスト（動画の下） -->
    <div class="marquee-section">
      <marquee class="text-gray-400" behavior="scroll" direction="left" scrollamount="3" scrolldelay="0">
        本日はご多用の中、ご来場ありがとうございました！
      </marquee>
    </div>

    <!-- 質問エリア -->
    <div class="question">
      <div class="q-text">
        <span class="inline-block mr-2">Q{{ currentStep }}.</span
        >{{ questions[currentStep - 1].text }}
      </div>

      <!-- 入力欄 -->
      <div v-if="questions[currentStep - 1].type === 'text'">
        <input
          v-model="form[questions[currentStep - 1].key]"
          type="text"
          placeholder="入力してください"
        />
      </div>
      <div v-else-if="questions[currentStep - 1].type === 'select'">
        <select v-model="form[questions[currentStep - 1].key]">
          <option value="" disabled>選択してください</option>
          <option
            v-for="option in questions[currentStep - 1].options"
            :key="option"
            :value="option"
          >
            {{ option }}
          </option>
        </select>
      </div>
    </div>

    <!-- ナビゲーション -->
    <div class="navigation">
      <button class="back" @click="back" :disabled="currentStep === 1">
        戻る
      </button>
      <button
        class="next"
        @click="next"
        :disabled="!isCurrentQuestionAnswered()"
      >
        {{ currentStep === totalSteps ? "送信" : "次へ" }}
      </button>
    </div>

    <!-- プログレス表示 -->
    <div class="progress-container">
      <div class="progress-text">ご協力ありがとうございます！</div>
      <div class="progress">
        <div
          class="progress-bar"
          :style="{
            width: (currentStep / totalSteps) * 100 + '%'
          }"
        ></div>
      </div>
      <div class="progress-status">{{ currentStep }}/{{ totalSteps }}</div>
    </div>

    <!-- キャラクター -->
    <div class="character">
      <img src="/images/questionnaire/background.png" alt="キャラクター" />
    </div>

    <!-- 完了ポップアップ -->
    <div v-if="showCompletionPopup" class="popup-overlay" @click="closeCompletionPopup">
      <div class="popup-content" @click.stop>
        <div class="popup-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" fill="#4ade80"/>
            <path d="m9 12 2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        
        <h2 class="popup-title">回答ありがとうございます！</h2>
        
        <div class="popup-message">
          <p>アンケートにご協力いただき、誠にありがとうございました。</p>
          <div class="response-summary">
            <div class="summary-item">
              <span class="label">学校:</span>
              <span class="value">{{ form.school }}</span>
            </div>
            <div class="summary-item">
              <span class="label">学科:</span>
              <span class="value">{{ form.department }}</span>
            </div>
            <div class="summary-item">
              <span class="label">学年:</span>
              <span class="value">{{ form.grade }}</span>
            </div>
            <div class="summary-item">
              <span class="label">性別:</span>
              <span class="value">{{ form.gender }}</span>
            </div>
          </div>
          <p class="completion-text">回答が正常に送信されました。</p>
        </div>
        
        <button class="popup-button" @click="closeCompletionPopup">
          完了
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
* {
  box-sizing: border-box;
}

.survey {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  padding: 0;
  margin: 0;
  position: relative;
  overflow-x: hidden;
}

// 背景オーバーレイ（メインページ用）
.background-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  z-index: -1;
}

.header {
  position: relative;
  width: 100%;
  // 16:9比率で横幅100%の場合の高さを計算 (100vw * 9/16)
  height: calc(100vw * 9 / 16);
  max-height: 300px; // 最大高さを制限
  min-height: 150px; // 最小高さを確保
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); // フォールバック背景
  
  @media (max-width: 768px) {
    max-height: 250px;
  }
  
  @media (max-width: 480px) {
    max-height: 200px;
  }
}

// ヘッダー動画のスタイル
.header-video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 1;
  
  // 16:9比率の動画をヘッダー領域に完全にフィット
  // ヘッダーが16:9比率なので動画も完全に一致
}

// ヘッダーオーバーレイ
.header-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(185, 185, 185, 0.507);
  z-index: 2;
}

// マーキーセクション
.marquee-section {
  width: 100%;
  background: white;
  border-top: 1px solid #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
  padding: 0;
  
  marquee {
    color: #1f2937;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 12px 0;
    margin: 0;
    line-height: 1.4;
    
    @media (max-width: 768px) {
      font-size: 1rem;
      padding: 10px 0;
    }
    
    @media (max-width: 480px) {
      font-size: 0.9rem;
      padding: 8px 0;
    }
  }
}

// 動画が読み込めない場合のフォールバックスタイル
.survey:not(.video-loaded) .header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  
  .header-video {
    display: none;
  }
  
  .header-overlay {
    display: none;
  }
}

.question {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px 20px;
  text-align: center;
  
  @media (max-width: 480px) {
    padding: 32px 16px;
  }

  .q-text {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 32px;
    color: white;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    line-height: 1.4;
    
    @media (max-width: 480px) {
      font-size: 1.5rem;
      margin-bottom: 24px;
    }
    
    span {
      display: inline-block;
      background: rgba(255, 255, 255, 0.2);
      padding: 4px 12px;
      border-radius: 20px;
      margin-right: 8px;
      font-size: 0.9em;
    }
  }

  input,
  select {
    width: 100%;
    max-width: 400px;
    padding: 16px 20px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    outline: none;
    
    @media (max-width: 480px) {
      padding: 14px 16px;
      font-size: 1rem;
    }

    &:focus {
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      transform: translateY(-2px);
      background: white;
    }

    &::placeholder {
      color: #a0aec0;
    }
  }

  select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 40px;
  }
}

.navigation {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  
  @media (max-width: 480px) {
    padding: 16px;
    gap: 12px;
  }

  button {
    flex: 1;
    padding: 16px 24px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    
    @media (max-width: 480px) {
      padding: 14px 20px;
      font-size: 1rem;
    }

    &:active {
      transform: scale(0.98);
    }
  }

  .back {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);

    &:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px);
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }

  .next {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);

    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(79, 172, 254, 0.6);
    }

    &:disabled {
      background: rgba(255, 255, 255, 0.3);
      color: rgba(255, 255, 255, 0.7);
      cursor: not-allowed;
      box-shadow: none;
    }
  }
}

.progress-container {
  padding: 20px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  
  @media (max-width: 480px) {
    padding: 16px;
  }
}

.progress-text {
  color: white;
  font-size: 1rem;
  margin-bottom: 16px;
  text-align: center;
  font-weight: 500;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  
  @media (max-width: 480px) {
    font-size: 0.9rem;
    margin-bottom: 12px;
  }
}

.progress {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  height: 8px;
  overflow: hidden;
  margin-bottom: 12px;

  .progress-bar {
    background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
    height: 100%;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 20px;
    box-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
  }
}

.progress-status {
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
  text-align: center;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.character {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 200px;
  height: 200px;
  opacity: 0.1;
  pointer-events: none;
  z-index: 0;
  
  @media (max-width: 480px) {
    width: 150px;
    height: 150px;
  }

  img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
}

// アニメーション効果
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeOut {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-20px);
  }
}

.question {
  animation: fadeInUp 0.6s ease-out;
  
  &.fade-out {
    animation: fadeOut 0.2s ease-in;
  }
}

// ボタンのリップル効果
@keyframes ripple {
  0% {
    transform: scale(0);
    opacity: 1;
  }
  100% {
    transform: scale(4);
    opacity: 0;
  }
}

button {
  position: relative;
  overflow: hidden;
  
  &::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }
  
  &:active::before {
    width: 300px;
    height: 300px;
  }
}

// ポップアップスタイル
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease-out;
  padding: 20px;
}

.popup-content {
  background: white;
  border-radius: 24px;
  padding: 40px 32px;
  max-width: 480px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
  animation: popupSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  
  @media (max-width: 480px) {
    padding: 32px 24px;
    margin: 0 16px;
    border-radius: 20px;
  }
}

.popup-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 24px;
  animation: bounceIn 0.6s ease-out 0.2s both;
  
  svg {
    width: 100%;
    height: 100%;
    drop-shadow: 0 4px 8px rgba(74, 222, 128, 0.3);
  }
}

.popup-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 20px;
  animation: slideInUp 0.5s ease-out 0.3s both;
  
  @media (max-width: 480px) {
    font-size: 1.5rem;
  }
}

.popup-message {
  margin-bottom: 32px;
  animation: slideInUp 0.5s ease-out 0.4s both;
  
  p {
    color: #6b7280;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 24px;
  }
  
  .completion-text {
    color: #059669;
    font-weight: 600;
    margin-top: 20px;
    margin-bottom: 0;
  }
}

.response-summary {
  background: #f9fafb;
  border-radius: 16px;
  padding: 20px;
  margin: 20px 0;
  border: 1px solid #e5e7eb;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
  
  &:last-child {
    border-bottom: none;
  }
  
  .label {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
  }
  
  .value {
    color: #1f2937;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
    word-break: break-word;
  }
}

.popup-button {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
  border: none;
  border-radius: 16px;
  padding: 16px 40px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
  animation: slideInUp 0.5s ease-out 0.5s both;
  
  @media (max-width: 480px) {
    padding: 14px 32px;
    font-size: 1rem;
  }
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 172, 254, 0.6);
  }
  
  &:active {
    transform: translateY(0);
  }
}

// ポップアップアニメーション
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes popupSlideIn {
  from {
    opacity: 0;
    transform: scale(0.8) translateY(40px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes bounceIn {
  0% {
    opacity: 0;
    transform: scale(0.3);
  }
  50% {
    opacity: 1;
    transform: scale(1.05);
  }
  70% {
    transform: scale(0.9);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

// ダークモード対応
@media (prefers-color-scheme: dark) {
  .survey {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
  }
  
  .popup-content {
    background: #1f2937;
    color: white;
  }
  
  .popup-title {
    color: white;
  }
  
  .response-summary {
    background: #374151;
    border-color: #4b5563;
  }
  
  .summary-item {
    border-color: #4b5563;
    
    .label {
      color: #d1d5db;
    }
    
    .value {
      color: white;
    }
  }
}
</style>
