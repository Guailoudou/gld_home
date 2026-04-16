<script setup lang="ts">
import { ref } from 'vue'

const formData = ref({
  name: '',
  email: '',
  message: ''
})

const messages = ref([
  {
    name: '访客 A',
    date: '2024-01-15',
    message: '网站很漂亮，功能很实用！'
  },
  {
    name: '访客 B',
    date: '2024-01-14',
    message: '小工具很好用，谢谢分享！'
  },
  {
    name: '访客 C',
    date: '2024-01-13',
    message: '作品展示很棒，学到了很多！'
  }
])

const submitMessage = () => {
  if (!formData.value.name || !formData.value.message) {
    alert('请填写昵称和留言内容')
    return
  }

  const now = new Date()
  const dateStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
  
  messages.value.unshift({
    name: formData.value.name,
    date: dateStr,
    message: formData.value.message
  })

  formData.value = {
    name: '',
    email: '',
    message: ''
  }

  alert('留言成功！')
}
</script>

<template>
  <div class="guestbook-container">
    <div class="content-wrapper">
      <div class="page-header">
        <h1 class="page-title">留言板</h1>
        <p class="page-description">留下你的想法和建议 （未启用）</p>
      </div>

      <!-- 留言表单 -->
      <div class="message-form">
        <div class="form-group">
          <label for="name">昵称 *</label>
          <input
            id="name"
            v-model="formData.name"
            type="text"
            placeholder="请输入昵称"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label for="email">邮箱（选填）</label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            placeholder="请输入邮箱"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label for="message">留言内容 *</label>
          <textarea
            id="message"
            v-model="formData.message"
            rows="5"
            placeholder="写下你的留言..."
            class="form-textarea"
          ></textarea>
        </div>

        <button class="submit-btn" @click="submitMessage">
          提交留言
        </button>
      </div>

      <!-- 留言列表 -->
      <div class="messages-list">
        <h2 class="list-title">最新留言</h2>
        
        <div 
          v-for="(msg, index) in messages" 
          :key="index"
          class="message-card"
        >
          <div class="message-header">
            <div class="message-author">
              <span class="author-icon">👤</span>
              <span class="author-name">{{ msg.name }}</span>
            </div>
            <span class="message-date">{{ msg.date }}</span>
          </div>
          <p class="message-content">{{ msg.message }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.guestbook-container {
  min-height: 100vh;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.content-wrapper {
  width: 100%;
  max-width: 800px;
}

.page-header {
  text-align: center;
  margin-bottom: 50px;

  .page-title {
    font-size: 2.5rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 15px 0;
    text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);

    @media (max-width: 768px) {
      font-size: 2rem;
    }
  }

  .page-description {
    font-size: 1.1rem;
    color: rgba(30, 30, 30, 0.85);
    margin: 0;
  }
}

.message-form {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 30px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  margin-bottom: 40px;

  .form-group {
    margin-bottom: 20px;

    label {
      display: block;
      font-size: 0.95rem;
      font-weight: 600;
      color: rgba(30, 30, 30, 0.9);
      margin-bottom: 8px;
    }

    .form-input,
    .form-textarea {
      width: 100%;
      padding: 12px 16px;
      background: rgba(255, 255, 255, 0.5);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 8px;
      font-size: 1rem;
      color: #333;
      transition: all 0.3s ease;
      box-sizing: border-box;

      &:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.7);
        border-color: rgba(255, 255, 255, 0.6);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
      }

      &::placeholder {
        color: rgba(0, 0, 0, 0.4);
      }
    }

    .form-textarea {
      resize: vertical;
      min-height: 120px;
    }
  }

  .submit-btn {
    width: 100%;
    padding: 14px;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
      background: rgba(255, 255, 255, 1);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
  }
}

.messages-list {
  .list-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 25px 0;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(30, 30, 30, 0.2);
  }

  .message-card {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(15px);
    border-radius: 12px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    margin-bottom: 15px;
    transition: all 0.3s ease;

    &:hover {
      background: rgba(255, 255, 255, 0.35);
      transform: translateX(5px);
    }

    .message-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;

      .message-author {
        display: flex;
        align-items: center;
        gap: 8px;

        .author-icon {
          font-size: 1.2rem;
        }

        .author-name {
          font-size: 1rem;
          font-weight: 600;
          color: rgba(30, 30, 30, 0.9);
        }
      }

      .message-date {
        font-size: 0.85rem;
        color: rgba(30, 30, 30, 0.7);
      }
    }

    .message-content {
      font-size: 0.95rem;
      line-height: 1.6;
      color: rgba(30, 30, 30, 0.85);
      margin: 0;
    }
  }
}
</style>
