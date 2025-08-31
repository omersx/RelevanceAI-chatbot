# 🧠 RelevanceAI Chatbot for WordPress
A professional, customizable AI chatbot widget powered by RelevanceAI that integrates seamlessly with WordPress websites.
#### 💪 the chatbot using Relevance AI Retrieval-Augmented Generation (RAG) service 🚀.
This chatbot can fetch, retrieve, and generate answers from your WordPress content or external knowledge base, making your website more interactive and intelligent.

# 📷 UI Previews

<img width="2560" height="1440" alt="rag" src="https://github.com/user-attachments/assets/e05762e9-a816-47c3-9a76-311652d74c5a" />
Lightweight php-based AI assistant widget for WordPress powered by RelevanceAi rag service.


-----------------------------
## 🌟 Features

- 🤖 AI-powered chatbot with Relevance AI RAG service
- 🔍 Retrieves and searches WordPress content using REST API
- 💬 Provides contextual, relevant, and dynamic responses
- ⚡ Lightweight single-file PHP/JS chatbot integration
- 🌐 Easily embeddable on WordPress pages or posts
- 🎨 Fully Customizable: Colors, position, messages, and themes
- 📱 Mobile Responsive: Optimized for all device sizes
- ⚡ Relevance Ai offers MCP server and more advanced services

## 📥 Installation & Usage

## ✅ WordPress Integration

1. Open your WordPress admin panel.
2. Go to **Plugins** > **WPCode** (or any other Code Snippet manager).
3. Create a new **php snippet**.
4. Copy the content of `RelevanceAi-chatbot.php` into the snippet
- ❗ Or embed it directly in your theme (RelevanceAi-chatbot.php) — not recommended for beginners
5. ✨ Save and activate — the chatbot will appear on your site! 🥳

## ⚙️ Configuration
## 🔑 RelevanceAI API Setup
IMPORTANT: You must replace the placeholder credentials with your actual RelevanceAI API credentials.

1. Log in to RelevanceAI: Visit RelevanceAI
2. Get Your Credentials: Navigate to your project settings
3. Find the Configuration Section in the code (around line 1530):
    ```php
    // 🔑 REPLACE THESE WITH YOUR ACTUAL RELEVANCE AI CREDENTIALS
    $this->project_id = 'YOUR_PROJECT_ID_HERE';     // Replace with your project ID
    $this->auth_token = 'YOUR_AUTH_TOKEN_HERE';     // Replace with your auth token  
    $this->agent_id = 'YOUR_AGENT_ID_HERE';         // Replace with your agent ID
    $this->region_id = 'YOUR_REGION_ID_HERE';       // Replace with your region ID

 4. Replace Each Value:
`YOUR_PROJECT_ID_HERE` → Your actual project ID
`YOUR_AUTH_TOKEN_HERE` → Your actual auth token
`YOUR_AGENT_ID_HERE` → Your actual agent ID
`YOUR_REGION_ID_HERE` → Your actual region ID (e.g., 'd7b62b')


-----------


### 📌 Notes

- Relevance Ai in free plan is limited that provide you daily credit to use the service but limited to few messages per day.so you have to upgrade for more use,
- sing up in Relevance Ai create agent and setup your rag then create your API Key
####  for easy setup use ready template agent sing up with this link : https://app.relevanceai.com/agents/bcbe5a/6b8c8ef52cf7-40db-93cf-3d437f38ab7e/db4b5fa6-23b1-4a8c-9f89-f5d7a6fb27df/clone

  #### the Agent Dashboard look like :

  <img width="984" height="482" alt="New Project (24)" src="https://github.com/user-attachments/assets/8d51ce3c-bd7d-469b-a56d-232a5f843d6a" />


###  you can find the Project ID , Agent ID and Region ID in link above in the browser of agent dashboard , that look like :
  
   <img width="3840" height="920" alt="Screenshot (286)_upscayl_2x_upscayl-standard-4x" src="https://github.com/user-attachments/assets/8eb9b4d6-cd87-47bd-ad78-184f34d739cd" />

### - for the full tutorial check it in YouTube :
  
https://youtu.be/jKXbm1zN4wk?si=6TWSvR9wzcz27aWk
  

--------------------



## 🎨 Customization Options

You can customize the chatbot by modifying the configuration in the `relevance_ai_chatbot_add_nonce()` function:
   ```php
    'customization' => array(
    'primaryColor' => '#7D2AE8',           // Main theme color
    'chatTitle' => 'AI Assistant',         // Header title
    'welcomeMessage' => '👋 Hello! I\'m your AI assistant. How can I help you today?',
    'position' => 'bottom-right',          // bottom-right, bottom-left, top-right, top-left
    'theme' => 'auto'                      // auto, light, dark
      ),
      'settings' => array(
    'desktopChatHeight' => '450px',        // Desktop chat window height
    'maxMessageLength' => 2000,            // Maximum message length
    'maxRetries' => 3,                     // API retry attempts
    'autoScroll' => true,  )              // Auto-scroll to new messages
 ```


### 📈 Performance
  - Lightweight: Minimal impact on page load times
  - Efficient: Optimized JavaScript and CSS
  - Caching-friendly: Static assets can be cached
  - Mobile-optimized: Fast performance on mobile devices


---------------------
  ### 📌 Example Use Cases
  
- Ideal for minimal deployments, demos, or fast LLM integrations into WordPress like:

- Real estate websites 🏡 → search and filter properties with RAG

- Legal consultancy websites ⚖️ → retrieve case studies & documents

- Knowledge base bots 📚 → FAQ-style support chatbot

## 📜 License

-This project is open-source under the MIT License.

## 🤝 Contributions

- Feel free to open issues, suggest features, or submit pull requests. Let’s build smarter web experiences together!
