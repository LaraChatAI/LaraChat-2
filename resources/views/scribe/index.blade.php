<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Laravel API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "https://larachat-restricted.coding.cab";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.3.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.3.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-claude-ai-integration" class="tocify-header">
                <li class="tocify-item level-1" data-unique="claude-ai-integration">
                    <a href="#claude-ai-integration">Claude AI Integration</a>
                </li>
                                    <ul id="tocify-subheader-claude-ai-integration" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="claude-ai-integration-POSTapi-claude">
                                <a href="#claude-ai-integration-POSTapi-claude">Send message to Claude</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="claude-ai-integration-GETapi-claude-sessions--filename-">
                                <a href="#claude-ai-integration-GETapi-claude-sessions--filename-">Get session messages</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-conversations" class="tocify-header">
                <li class="tocify-item level-1" data-unique="conversations">
                    <a href="#conversations">Conversations</a>
                </li>
                                    <ul id="tocify-subheader-conversations" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="conversations-GETapi-conversations">
                                <a href="#conversations-GETapi-conversations">List conversations</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations">
                                <a href="#conversations-POSTapi-conversations">Create conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-GETapi-conversations-archived">
                                <a href="#conversations-GETapi-conversations-archived">List archived conversations</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations--conversation_id--archive">
                                <a href="#conversations-POSTapi-conversations--conversation_id--archive">Archive conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations--conversation_id--unarchive">
                                <a href="#conversations-POSTapi-conversations--conversation_id--unarchive">Unarchive conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-GETapi-claude-conversations">
                                <a href="#conversations-GETapi-claude-conversations">List conversations</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-POSTapi-github-webhook">
                                <a href="#endpoints-POSTapi-github-webhook">POST api/github/webhook</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-github-webhook">
                                <a href="#endpoints-GETapi-github-webhook">GET api/github/webhook</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-webhooks">
                                <a href="#endpoints-POSTapi-webhooks">POST api/webhooks</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-docs">
                                <a href="#endpoints-GETapi-docs">GET api/docs</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-repository-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="repository-management">
                    <a href="#repository-management">Repository Management</a>
                </li>
                                    <ul id="tocify-subheader-repository-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="repository-management-GETapi-repositories">
                                <a href="#repository-management-GETapi-repositories">List repositories</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="repository-management-POSTapi-repositories">
                                <a href="#repository-management-POSTapi-repositories">Clone repository</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="repository-management-DELETEapi-repositories--repository_slug-">
                                <a href="#repository-management-DELETEapi-repositories--repository_slug-">Delete repository</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="repository-management-POSTapi-repositories--repository_slug--pull">
                                <a href="#repository-management-POSTapi-repositories--repository_slug--pull">Pull repository updates</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="repository-management-POSTapi-repositories--repository_slug--copy-to-hot">
                                <a href="#repository-management-POSTapi-repositories--repository_slug--copy-to-hot">Copy repository to hot folder</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-system-commands" class="tocify-header">
                <li class="tocify-item level-1" data-unique="system-commands">
                    <a href="#system-commands">System Commands</a>
                </li>
                                    <ul id="tocify-subheader-system-commands" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="system-commands-POSTapi-run-command">
                                <a href="#system-commands-POSTapi-run-command">Execute command</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: August 16, 2025</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>https://larachat-restricted.coding.cab</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="claude-ai-integration">Claude AI Integration</h1>

    <p>APIs for interacting with Claude AI assistant</p>

                                <h2 id="claude-ai-integration-POSTapi-claude">Send message to Claude</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Send a message to Claude AI and receive a response</p>

<span id="example-requests-POSTapi-claude">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/claude" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"prompt\": \"Can you help me debug this code?\",
    \"sessionId\": \"session_abc123\",
    \"sessionFilename\": \"claude-sessions\\/2024-01-15T10-30-00-session-abc123.json\",
    \"conversationId\": 1,
    \"repositoryPath\": \"\\/projects\\/my-app\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/claude"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "prompt": "Can you help me debug this code?",
    "sessionId": "session_abc123",
    "sessionFilename": "claude-sessions\/2024-01-15T10-30-00-session-abc123.json",
    "conversationId": 1,
    "repositoryPath": "\/projects\/my-app"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-claude">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: true,
    &quot;message&quot;: &quot;Message queued for processing&quot;,
    &quot;conversationId&quot;: 1,
    &quot;sessionFilename&quot;: &quot;claude-sessions/2024-01-15T10-30-00-session-abc123.json&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403, Unauthorized):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Forbidden&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Validation Error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The prompt field is required.&quot;,
    &quot;errors&quot;: {
        &quot;prompt&quot;: [
            &quot;The prompt field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-claude" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-claude"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-claude"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-claude" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-claude">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-claude" data-method="POST"
      data-path="api/claude"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-claude', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-claude"
                    onclick="tryItOut('POSTapi-claude');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-claude"
                    onclick="cancelTryOut('POSTapi-claude');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-claude"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/claude</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-claude"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-claude"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>prompt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="prompt"                data-endpoint="POSTapi-claude"
               value="Can you help me debug this code?"
               data-component="body">
    <br>
<p>The message to send to Claude. Example: <code>Can you help me debug this code?</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sessionId</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="sessionId"                data-endpoint="POSTapi-claude"
               value="session_abc123"
               data-component="body">
    <br>
<p>optional The Claude session ID. Example: <code>session_abc123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sessionFilename</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="sessionFilename"                data-endpoint="POSTapi-claude"
               value="claude-sessions/2024-01-15T10-30-00-session-abc123.json"
               data-component="body">
    <br>
<p>optional The session filename. Example: <code>claude-sessions/2024-01-15T10-30-00-session-abc123.json</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>conversationId</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversationId"                data-endpoint="POSTapi-claude"
               value="1"
               data-component="body">
    <br>
<p>optional The conversation ID. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>repositoryPath</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="repositoryPath"                data-endpoint="POSTapi-claude"
               value="/projects/my-app"
               data-component="body">
    <br>
<p>optional The repository path for context. Example: <code>/projects/my-app</code></p>
        </div>
        </form>

                    <h2 id="claude-ai-integration-GETapi-claude-sessions--filename-">Get session messages</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retrieve all messages from a Claude AI session</p>

<span id="example-requests-GETapi-claude-sessions--filename-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://larachat-restricted.coding.cab/api/claude/sessions/claude-sessions/2024-01-15T10-30-00-session-abc123.json" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/claude/sessions/claude-sessions/2024-01-15T10-30-00-session-abc123.json"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-claude-sessions--filename-">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;prompt&quot;: &quot;Can you help me debug this code?&quot;,
        &quot;rawJsonResponses&quot;: [
            &quot;Looking at your code...&quot;
        ],
        &quot;timestamp&quot;: &quot;2024-01-15T10:30:00.000000Z&quot;,
        &quot;sessionId&quot;: &quot;session_abc123&quot;
    }
]</code>
 </pre>
            <blockquote>
            <p>Example response (404, Session Not Found):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Session file not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Invalid Session File):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Invalid session file&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-claude-sessions--filename-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-claude-sessions--filename-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-claude-sessions--filename-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-claude-sessions--filename-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-claude-sessions--filename-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-claude-sessions--filename-" data-method="GET"
      data-path="api/claude/sessions/{filename}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-claude-sessions--filename-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-claude-sessions--filename-"
                    onclick="tryItOut('GETapi-claude-sessions--filename-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-claude-sessions--filename-"
                    onclick="cancelTryOut('GETapi-claude-sessions--filename-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-claude-sessions--filename-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/claude/sessions/{filename}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-claude-sessions--filename-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-claude-sessions--filename-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>filename</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="filename"                data-endpoint="GETapi-claude-sessions--filename-"
               value="claude-sessions/2024-01-15T10-30-00-session-abc123.json"
               data-component="url">
    <br>
<p>The session filename. Example: <code>claude-sessions/2024-01-15T10-30-00-session-abc123.json</code></p>
            </div>
                    </form>

                <h1 id="conversations">Conversations</h1>

    <p>APIs for managing Claude AI conversations</p>

                                <h2 id="conversations-GETapi-conversations">List conversations</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Get a list of all non-archived conversations for the authenticated user</p>

<span id="example-requests-GETapi-conversations">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://larachat-restricted.coding.cab/api/conversations" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/conversations"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-conversations">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: 1,
        &quot;user_id&quot;: 1,
        &quot;title&quot;: &quot;How to implement authentication&quot;,
        &quot;message&quot;: &quot;Can you help me implement JWT authentication?&quot;,
        &quot;claude_session_id&quot;: &quot;session_abc123&quot;,
        &quot;project_directory&quot;: &quot;/projects/abc123&quot;,
        &quot;repository&quot;: &quot;myapp&quot;,
        &quot;filename&quot;: &quot;claude-sessions/2024-01-15T10-30-00-session-abc123.json&quot;,
        &quot;is_processing&quot;: false,
        &quot;archived&quot;: false,
        &quot;created_at&quot;: &quot;2024-01-15T10:30:00.000000Z&quot;,
        &quot;updated_at&quot;: &quot;2024-01-15T10:35:00.000000Z&quot;
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-conversations" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-conversations"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-conversations"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-conversations" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-conversations">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-conversations" data-method="GET"
      data-path="api/conversations"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-conversations', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-conversations"
                    onclick="tryItOut('GETapi-conversations');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-conversations"
                    onclick="cancelTryOut('GETapi-conversations');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-conversations"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/conversations</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="conversations-POSTapi-conversations">Create conversation</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Start a new conversation with Claude AI</p>

<span id="example-requests-POSTapi-conversations">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/conversations" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"message\": \"How do I implement authentication?\",
    \"repository\": \"myapp\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/conversations"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "message": "How do I implement authentication?",
    "repository": "myapp"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations">
            <blockquote>
            <p>Example response (302, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;redirect&quot;: &quot;/claude/1&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Validation Error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The message field is required.&quot;,
    &quot;errors&quot;: {
        &quot;message&quot;: [
            &quot;The message field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations" data-method="POST"
      data-path="api/conversations"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations"
                    onclick="tryItOut('POSTapi-conversations');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations"
                    onclick="cancelTryOut('POSTapi-conversations');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>message</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="message"                data-endpoint="POSTapi-conversations"
               value="How do I implement authentication?"
               data-component="body">
    <br>
<p>The initial message to send to Claude. Can be base64 encoded. Example: <code>How do I implement authentication?</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>repository</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="repository"                data-endpoint="POSTapi-conversations"
               value="myapp"
               data-component="body">
    <br>
<p>optional The repository to use for this conversation. Example: <code>myapp</code></p>
        </div>
        </form>

                    <h2 id="conversations-GETapi-conversations-archived">List archived conversations</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Get a list of all archived conversations for the authenticated user</p>

<span id="example-requests-GETapi-conversations-archived">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://larachat-restricted.coding.cab/api/conversations/archived" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/conversations/archived"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-conversations-archived">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: 2,
        &quot;user_id&quot;: 1,
        &quot;title&quot;: &quot;Old conversation about testing&quot;,
        &quot;message&quot;: &quot;How do I write unit tests?&quot;,
        &quot;claude_session_id&quot;: &quot;session_xyz789&quot;,
        &quot;project_directory&quot;: &quot;/projects/xyz789&quot;,
        &quot;repository&quot;: &quot;testapp&quot;,
        &quot;filename&quot;: &quot;claude-sessions/2024-01-10T14-20-00-session-xyz789.json&quot;,
        &quot;is_processing&quot;: false,
        &quot;archived&quot;: true,
        &quot;created_at&quot;: &quot;2024-01-10T14:20:00.000000Z&quot;,
        &quot;updated_at&quot;: &quot;2024-01-10T14:25:00.000000Z&quot;
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-conversations-archived" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-conversations-archived"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-conversations-archived"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-conversations-archived" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-conversations-archived">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-conversations-archived" data-method="GET"
      data-path="api/conversations/archived"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-conversations-archived', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-conversations-archived"
                    onclick="tryItOut('GETapi-conversations-archived');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-conversations-archived"
                    onclick="cancelTryOut('GETapi-conversations-archived');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-conversations-archived"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/conversations/archived</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-conversations-archived"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-conversations-archived"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="conversations-POSTapi-conversations--conversation_id--archive">Archive conversation</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Archive a conversation to hide it from the main list</p>

<span id="example-requests-POSTapi-conversations--conversation_id--archive">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/conversations/16/archive" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/conversations/16/archive"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations--conversation_id--archive">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Conversation archived successfully&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403, Unauthorized):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Unauthorized&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations--conversation_id--archive" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations--conversation_id--archive"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations--conversation_id--archive"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations--conversation_id--archive" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations--conversation_id--archive">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations--conversation_id--archive" data-method="POST"
      data-path="api/conversations/{conversation_id}/archive"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations--conversation_id--archive', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations--conversation_id--archive"
                    onclick="tryItOut('POSTapi-conversations--conversation_id--archive');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations--conversation_id--archive"
                    onclick="cancelTryOut('POSTapi-conversations--conversation_id--archive');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations--conversation_id--archive"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations/{conversation_id}/archive</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations--conversation_id--archive"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations--conversation_id--archive"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="POSTapi-conversations--conversation_id--archive"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation"                data-endpoint="POSTapi-conversations--conversation_id--archive"
               value="1"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="conversations-POSTapi-conversations--conversation_id--unarchive">Unarchive conversation</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Restore an archived conversation to the main list</p>

<span id="example-requests-POSTapi-conversations--conversation_id--unarchive">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/conversations/16/unarchive" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/conversations/16/unarchive"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations--conversation_id--unarchive">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Conversation unarchived successfully&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403, Unauthorized):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Unauthorized&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations--conversation_id--unarchive" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations--conversation_id--unarchive"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations--conversation_id--unarchive"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations--conversation_id--unarchive" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations--conversation_id--unarchive">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations--conversation_id--unarchive" data-method="POST"
      data-path="api/conversations/{conversation_id}/unarchive"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations--conversation_id--unarchive', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations--conversation_id--unarchive"
                    onclick="tryItOut('POSTapi-conversations--conversation_id--unarchive');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations--conversation_id--unarchive"
                    onclick="cancelTryOut('POSTapi-conversations--conversation_id--unarchive');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations--conversation_id--unarchive"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations/{conversation_id}/unarchive</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations--conversation_id--unarchive"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations--conversation_id--unarchive"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="POSTapi-conversations--conversation_id--unarchive"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation"                data-endpoint="POSTapi-conversations--conversation_id--unarchive"
               value="1"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="conversations-GETapi-claude-conversations">List conversations</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Get a list of all non-archived conversations for the authenticated user</p>

<span id="example-requests-GETapi-claude-conversations">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://larachat-restricted.coding.cab/api/claude/conversations" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/claude/conversations"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-claude-conversations">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: 1,
        &quot;user_id&quot;: 1,
        &quot;title&quot;: &quot;How to implement authentication&quot;,
        &quot;message&quot;: &quot;Can you help me implement JWT authentication?&quot;,
        &quot;claude_session_id&quot;: &quot;session_abc123&quot;,
        &quot;project_directory&quot;: &quot;/projects/abc123&quot;,
        &quot;repository&quot;: &quot;myapp&quot;,
        &quot;filename&quot;: &quot;claude-sessions/2024-01-15T10-30-00-session-abc123.json&quot;,
        &quot;is_processing&quot;: false,
        &quot;archived&quot;: false,
        &quot;created_at&quot;: &quot;2024-01-15T10:30:00.000000Z&quot;,
        &quot;updated_at&quot;: &quot;2024-01-15T10:35:00.000000Z&quot;
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-claude-conversations" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-claude-conversations"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-claude-conversations"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-claude-conversations" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-claude-conversations">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-claude-conversations" data-method="GET"
      data-path="api/claude/conversations"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-claude-conversations', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-claude-conversations"
                    onclick="tryItOut('GETapi-claude-conversations');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-claude-conversations"
                    onclick="cancelTryOut('GETapi-claude-conversations');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-claude-conversations"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/claude/conversations</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-claude-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-claude-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-POSTapi-github-webhook">POST api/github/webhook</h2>

<p>
</p>



<span id="example-requests-POSTapi-github-webhook">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/github/webhook" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/github/webhook"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-github-webhook">
</span>
<span id="execution-results-POSTapi-github-webhook" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-github-webhook"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-github-webhook"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-github-webhook" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-github-webhook">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-github-webhook" data-method="POST"
      data-path="api/github/webhook"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-github-webhook', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-github-webhook"
                    onclick="tryItOut('POSTapi-github-webhook');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-github-webhook"
                    onclick="cancelTryOut('POSTapi-github-webhook');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-github-webhook"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/github/webhook</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-github-webhook"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-github-webhook"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-github-webhook">GET api/github/webhook</h2>

<p>
</p>



<span id="example-requests-GETapi-github-webhook">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://larachat-restricted.coding.cab/api/github/webhook" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/github/webhook"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-github-webhook">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Unauthorized&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-github-webhook" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-github-webhook"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-github-webhook"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-github-webhook" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-github-webhook">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-github-webhook" data-method="GET"
      data-path="api/github/webhook"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-github-webhook', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-github-webhook"
                    onclick="tryItOut('GETapi-github-webhook');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-github-webhook"
                    onclick="cancelTryOut('GETapi-github-webhook');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-github-webhook"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/github/webhook</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-github-webhook"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-github-webhook"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-webhooks">POST api/webhooks</h2>

<p>
</p>



<span id="example-requests-POSTapi-webhooks">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/webhooks" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/webhooks"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-webhooks">
</span>
<span id="execution-results-POSTapi-webhooks" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-webhooks"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-webhooks"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-webhooks" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-webhooks">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-webhooks" data-method="POST"
      data-path="api/webhooks"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-webhooks', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-webhooks"
                    onclick="tryItOut('POSTapi-webhooks');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-webhooks"
                    onclick="cancelTryOut('POSTapi-webhooks');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-webhooks"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/webhooks</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-webhooks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-webhooks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-docs">GET api/docs</h2>

<p>
</p>



<span id="example-requests-GETapi-docs">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://larachat-restricted.coding.cab/api/docs" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/docs"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-docs">
            <blockquote>
            <p>Example response (302):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
location: https://larachat-restricted.coding.cab/docs
content-type: text/html; charset=utf-8
vary: X-Inertia
access-control-allow-origin: *
set-cookie: XSRF-TOKEN=eyJpdiI6ImV2ZVdlZU13ZXU3Z2s1TFZNTUdjL2c9PSIsInZhbHVlIjoiclFSZ2hhS3VES3ZGQU5kYWhycXNpQmlERnpnV0xVVEJCVXZRaTM0NmxUVHh3Q2g1TGRlUFFZSjBVQ1dxaGEyYTRSZDFZRDNnVDRWRkJHRkhYa0syMDcyQVdPMGpSNVpGYzFrUTdnVkFiNTk0MTliSy9RRE9lSVNlK3Z1WWkwWFQiLCJtYWMiOiI1Yzk2ZWE0NzE1NDViYzFkNzkxMmZhZDE5ZGFmMTU4NWFiOTljNjJmNzhjNDkwNjA4M2RiNzk0NzYzZjM5M2EwIiwidGFnIjoiIn0%3D; expires=Sat, 16 Aug 2025 11:22:41 GMT; Max-Age=7200; path=/; secure; samesite=lax; laravel_session=eyJpdiI6IkQwbVRPV0t1cDU4MWNqODYwbEFkc3c9PSIsInZhbHVlIjoiUHdkcUNETzNxSm9GTXBxbTF6enM2VlUxNDNWNXJ4MlVTSmZuZkdxMktiSjh2VzVWSFpKTkZQU3Q2dGVYRnduTjlVUXVKYmpPVmRrODVkUmpTMGFJaUNmTjVpWmwva2RzN1IwSHFjVVRmUWVKYVZaMWNyRGlCbGY1eXFkZmE4OGMiLCJtYWMiOiJhOTE3NGQzOGQ4ZjQ5ZWYyYjEwYTg1NGRkY2MwZDgyODk3ZjQ5NDliYWRhYTcwZDhhY2RiODE1OGZhNTc4NDUzIiwidGFnIjoiIn0%3D; expires=Sat, 16 Aug 2025 11:22:41 GMT; Max-Age=7200; path=/; secure; httponly; samesite=lax
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">&lt;!DOCTYPE html&gt;
&lt;html&gt;
    &lt;head&gt;
        &lt;meta charset=&quot;UTF-8&quot; /&gt;
        &lt;meta http-equiv=&quot;refresh&quot; content=&quot;0;url=&#039;https://larachat-restricted.coding.cab/docs&#039;&quot; /&gt;

        &lt;title&gt;Redirecting to https://larachat-restricted.coding.cab/docs&lt;/title&gt;
    &lt;/head&gt;
    &lt;body&gt;
        Redirecting to &lt;a href=&quot;https://larachat-restricted.coding.cab/docs&quot;&gt;https://larachat-restricted.coding.cab/docs&lt;/a&gt;.
    &lt;/body&gt;
&lt;/html&gt;</code>
 </pre>
    </span>
<span id="execution-results-GETapi-docs" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-docs"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-docs"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-docs" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-docs">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-docs" data-method="GET"
      data-path="api/docs"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-docs', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-docs"
                    onclick="tryItOut('GETapi-docs');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-docs"
                    onclick="cancelTryOut('GETapi-docs');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-docs"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/docs</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-docs"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-docs"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="repository-management">Repository Management</h1>

    <p>APIs for managing Git repositories</p>

                                <h2 id="repository-management-GETapi-repositories">List repositories</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Get a list of all repositories with their hot folder status</p>

<span id="example-requests-GETapi-repositories">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://larachat-restricted.coding.cab/api/repositories" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/repositories"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-repositories">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;my-project&quot;,
        &quot;url&quot;: &quot;https://github.com/user/my-project.git&quot;,
        &quot;local_path&quot;: &quot;repositories/base/my-project&quot;,
        &quot;branch&quot;: &quot;main&quot;,
        &quot;last_pulled_at&quot;: &quot;2024-01-15T10:30:00.000000Z&quot;,
        &quot;has_hot_folder&quot;: true,
        &quot;slug&quot;: &quot;my-project&quot;,
        &quot;created_at&quot;: &quot;2024-01-10T08:00:00.000000Z&quot;,
        &quot;updated_at&quot;: &quot;2024-01-15T10:30:00.000000Z&quot;
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-repositories" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-repositories"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-repositories"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-repositories" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-repositories">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-repositories" data-method="GET"
      data-path="api/repositories"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-repositories', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-repositories"
                    onclick="tryItOut('GETapi-repositories');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-repositories"
                    onclick="cancelTryOut('GETapi-repositories');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-repositories"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/repositories</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-repositories"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-repositories"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="repository-management-POSTapi-repositories">Clone repository</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Clone a new Git repository to the local system</p>

<span id="example-requests-POSTapi-repositories">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/repositories" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"url\": \"https:\\/\\/github.com\\/user\\/repo.git\",
    \"branch\": \"develop\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/repositories"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "url": "https:\/\/github.com\/user\/repo.git",
    "branch": "develop"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-repositories">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Repository cloned successfully&quot;,
    &quot;repository&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;my-project&quot;,
        &quot;url&quot;: &quot;https://github.com/user/my-project.git&quot;,
        &quot;local_path&quot;: &quot;repositories/base/my-project&quot;,
        &quot;branch&quot;: &quot;main&quot;,
        &quot;last_pulled_at&quot;: &quot;2024-01-15T10:30:00.000000Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (409, Repository Exists):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Repository already exists&quot;,
    &quot;repository&quot;: {}
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Clone Failed):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Failed to clone repository&quot;,
    &quot;error&quot;: &quot;fatal: repository not found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-repositories" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-repositories"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-repositories"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-repositories" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-repositories">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-repositories" data-method="POST"
      data-path="api/repositories"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-repositories', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-repositories"
                    onclick="tryItOut('POSTapi-repositories');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-repositories"
                    onclick="cancelTryOut('POSTapi-repositories');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-repositories"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/repositories</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-repositories"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-repositories"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>url</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="url"                data-endpoint="POSTapi-repositories"
               value="https://github.com/user/repo.git"
               data-component="body">
    <br>
<p>The Git repository URL. Example: <code>https://github.com/user/repo.git</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>branch</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="branch"                data-endpoint="POSTapi-repositories"
               value="develop"
               data-component="body">
    <br>
<p>optional The branch to clone. If not specified, the default branch will be used. Example: <code>develop</code></p>
        </div>
        </form>

                    <h2 id="repository-management-DELETEapi-repositories--repository_slug-">Delete repository</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove a repository from the system</p>

<span id="example-requests-DELETEapi-repositories--repository_slug-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://larachat-restricted.coding.cab/api/repositories/architecto" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/repositories/architecto"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-repositories--repository_slug-">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Repository deleted successfully&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-repositories--repository_slug-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-repositories--repository_slug-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-repositories--repository_slug-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-repositories--repository_slug-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-repositories--repository_slug-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-repositories--repository_slug-" data-method="DELETE"
      data-path="api/repositories/{repository_slug}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-repositories--repository_slug-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-repositories--repository_slug-"
                    onclick="tryItOut('DELETEapi-repositories--repository_slug-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-repositories--repository_slug-"
                    onclick="cancelTryOut('DELETEapi-repositories--repository_slug-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-repositories--repository_slug-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/repositories/{repository_slug}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-repositories--repository_slug-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-repositories--repository_slug-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>repository_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="repository_slug"                data-endpoint="DELETEapi-repositories--repository_slug-"
               value="architecto"
               data-component="url">
    <br>
<p>The slug of the repository. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>repository</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="repository"                data-endpoint="DELETEapi-repositories--repository_slug-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the repository. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="repository-management-POSTapi-repositories--repository_slug--pull">Pull repository updates</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Pull the latest changes from the remote repository</p>

<span id="example-requests-POSTapi-repositories--repository_slug--pull">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/repositories/architecto/pull" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/repositories/architecto/pull"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-repositories--repository_slug--pull">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Repository updated successfully&quot;,
    &quot;repository&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;my-project&quot;,
        &quot;url&quot;: &quot;https://github.com/user/my-project.git&quot;,
        &quot;local_path&quot;: &quot;repositories/base/my-project&quot;,
        &quot;branch&quot;: &quot;main&quot;,
        &quot;last_pulled_at&quot;: &quot;2024-01-15T11:00:00.000000Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Pull Failed):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Failed to pull repository&quot;,
    &quot;error&quot;: &quot;error: Your local changes would be overwritten&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-repositories--repository_slug--pull" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-repositories--repository_slug--pull"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-repositories--repository_slug--pull"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-repositories--repository_slug--pull" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-repositories--repository_slug--pull">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-repositories--repository_slug--pull" data-method="POST"
      data-path="api/repositories/{repository_slug}/pull"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-repositories--repository_slug--pull', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-repositories--repository_slug--pull"
                    onclick="tryItOut('POSTapi-repositories--repository_slug--pull');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-repositories--repository_slug--pull"
                    onclick="cancelTryOut('POSTapi-repositories--repository_slug--pull');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-repositories--repository_slug--pull"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/repositories/{repository_slug}/pull</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-repositories--repository_slug--pull"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-repositories--repository_slug--pull"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>repository_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="repository_slug"                data-endpoint="POSTapi-repositories--repository_slug--pull"
               value="architecto"
               data-component="url">
    <br>
<p>The slug of the repository. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>repository</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="repository"                data-endpoint="POSTapi-repositories--repository_slug--pull"
               value="1"
               data-component="url">
    <br>
<p>The ID of the repository. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="repository-management-POSTapi-repositories--repository_slug--copy-to-hot">Copy repository to hot folder</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Check if a hot folder exists for the repository or trigger creation</p>

<span id="example-requests-POSTapi-repositories--repository_slug--copy-to-hot">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/repositories/architecto/copy-to-hot" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/repositories/architecto/copy-to-hot"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-repositories--repository_slug--copy-to-hot">
            <blockquote>
            <p>Example response (200, Hot Folder Exists):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Hot folder already exists&quot;,
    &quot;has_hot_folder&quot;: true
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Copy Job Dispatched):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Repository copy job dispatched&quot;,
    &quot;has_hot_folder&quot;: false
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-repositories--repository_slug--copy-to-hot" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-repositories--repository_slug--copy-to-hot"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-repositories--repository_slug--copy-to-hot"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-repositories--repository_slug--copy-to-hot" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-repositories--repository_slug--copy-to-hot">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-repositories--repository_slug--copy-to-hot" data-method="POST"
      data-path="api/repositories/{repository_slug}/copy-to-hot"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-repositories--repository_slug--copy-to-hot', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-repositories--repository_slug--copy-to-hot"
                    onclick="tryItOut('POSTapi-repositories--repository_slug--copy-to-hot');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-repositories--repository_slug--copy-to-hot"
                    onclick="cancelTryOut('POSTapi-repositories--repository_slug--copy-to-hot');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-repositories--repository_slug--copy-to-hot"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/repositories/{repository_slug}/copy-to-hot</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-repositories--repository_slug--copy-to-hot"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-repositories--repository_slug--copy-to-hot"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>repository_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="repository_slug"                data-endpoint="POSTapi-repositories--repository_slug--copy-to-hot"
               value="architecto"
               data-component="url">
    <br>
<p>The slug of the repository. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>repository</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="repository"                data-endpoint="POSTapi-repositories--repository_slug--copy-to-hot"
               value="1"
               data-component="url">
    <br>
<p>The ID of the repository. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="system-commands">System Commands</h1>

    <p>APIs for executing system commands</p>

                                <h2 id="system-commands-POSTapi-run-command">Execute command</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Run a system command and return the output</p>

<span id="example-requests-POSTapi-run-command">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://larachat-restricted.coding.cab/api/run-command" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"command\": \"ls -la\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://larachat-restricted.coding.cab/api/run-command"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "command": "ls -la"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-run-command">
            <blockquote>
            <p>Example response (200, Success):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;output&quot;: &quot;total 48\ndrwxr-xr-x  12 user  staff   384 Jan 15 10:30 .\ndrwxr-xr-x   5 user  staff   160 Jan 10 08:00 ..&quot;,
    &quot;success&quot;: true
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Command Failed):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;output&quot;: &quot;ls: invalid option -- &#039;z&#039;&quot;,
    &quot;success&quot;: false
}</code>
 </pre>
            <blockquote>
            <p>Example response (500, Execution Error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;output&quot;: &quot;Command execution failed&quot;,
    &quot;success&quot;: false
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-run-command" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-run-command"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-run-command"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-run-command" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-run-command">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-run-command" data-method="POST"
      data-path="api/run-command"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-run-command', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-run-command"
                    onclick="tryItOut('POSTapi-run-command');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-run-command"
                    onclick="cancelTryOut('POSTapi-run-command');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-run-command"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/run-command</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-run-command"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-run-command"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>command</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="command"                data-endpoint="POSTapi-run-command"
               value="ls -la"
               data-component="body">
    <br>
<p>The command to execute. Example: <code>ls -la</code></p>
        </div>
        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
