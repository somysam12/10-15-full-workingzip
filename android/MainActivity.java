package com.silentpanel.app;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Build;
import android.os.Bundle;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.CookieManager;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ProgressBar;
import android.widget.Toast;

public class MainActivity extends Activity {

    private WebView webView;
    private ProgressBar progressBar;
    private View splashScreen;
    private View refreshButton;
    private View homeButton;

    private SharedPreferences loginPrefs;
    private String siteKey;
    private String currentUrl;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        requestWindowFeature(Window.FEATURE_NO_TITLE);

        getWindow().setFlags(
                WindowManager.LayoutParams.FLAG_FULLSCREEN,
                WindowManager.LayoutParams.FLAG_FULLSCREEN
        );

        getWindow().setFlags(
                WindowManager.LayoutParams.FLAG_SECURE,
                WindowManager.LayoutParams.FLAG_SECURE
        );

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(0xFF0A0E27);
        }

        setContentView(R.layout.activity_main);

        webView = findViewById(R.id.webview);
        progressBar = findViewById(R.id.progressBar);
        splashScreen = findViewById(R.id.splash_screen);
        refreshButton = findViewById(R.id.btn_refresh);
        homeButton = findViewById(R.id.btn_home);

        if (webView == null) {
            Toast.makeText(this, "WebView not found", Toast.LENGTH_LONG).show();
            return;
        }

        // Refresh button
        if (refreshButton != null) {
            refreshButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    webView.reload();
                }
            });
        }

        // Home button
        if (homeButton != null) {
            homeButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    Intent i = new Intent(MainActivity.this, WebsiteSelectorActivity.class);
                    i.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
                    startActivity(i);
                    finish();
                }
            });
        }

        Intent intent = getIntent();
        currentUrl = intent.getStringExtra("load_url");
        siteKey = intent.getStringExtra("site_key");
        if (siteKey == null) siteKey = "default";

        loginPrefs = getSharedPreferences("LOGIN_STORE", MODE_PRIVATE);

        setupWebView();
        loadWebsite();
        
        // Fetch and show announcements from server
        fetchAnnouncements();
    }

    private void setupWebView() {
        try {
            WebSettings webSettings = webView.getSettings();

            webSettings.setJavaScriptEnabled(true);
            webSettings.setJavaScriptCanOpenWindowsAutomatically(true);
            webSettings.setDomStorageEnabled(true);
            webSettings.setDatabaseEnabled(true);
            webSettings.setLoadsImagesAutomatically(true);
            webSettings.setBlockNetworkImage(false);
            webSettings.setBlockNetworkLoads(false);
            webSettings.setUseWideViewPort(true);
            webSettings.setLoadWithOverviewMode(true);
            webSettings.setBuiltInZoomControls(true);
            webSettings.setDisplayZoomControls(false);
            webSettings.setSaveFormData(true);
            webSettings.setSavePassword(true);

            webSettings.setAllowFileAccess(true);
            webSettings.setAllowContentAccess(true);

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.JELLY_BEAN) {
                webSettings.setAllowFileAccessFromFileURLs(true);
                webSettings.setAllowUniversalAccessFromFileURLs(true);
            }

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
                webSettings.setMixedContentMode(
                        WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
                );
            }

            CookieManager.getInstance().setAcceptCookie(true);
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
                CookieManager.getInstance()
                        .setAcceptThirdPartyCookies(webView, true);
            }

            webView.setOnLongClickListener(new View.OnLongClickListener() {
                @Override
                public boolean onLongClick(View v) {
                    return true;
                }
            });
            webView.setLongClickable(false);

            webView.setWebViewClient(new WebViewClient() {

                @Override
                public void onPageStarted(WebView view, String url, android.graphics.Bitmap favicon) {
                    if (progressBar != null) {
                        progressBar.setVisibility(View.VISIBLE);
                        progressBar.setProgress(10);
                    }

                    if (splashScreen != null && splashScreen.getVisibility() == View.VISIBLE) {
                        splashScreen.animate()
                                .alpha(0f)
                                .setDuration(300)
                                .withEndAction(new Runnable() {
                                    @Override
                                    public void run() {
                                        splashScreen.setVisibility(View.GONE);
                                    }
                                })
                                .start();
                    }
                }

                @Override
                public void onPageFinished(WebView view, String url) {
                    if (progressBar != null) {
                        progressBar.setVisibility(View.GONE);
                    }
                    autoFillIfSaved();
                    captureIfTyped();
                }

                @Override
                public boolean shouldOverrideUrlLoading(WebView view, String url) {
                    view.loadUrl(url);
                    return true;
                }
            });

            webView.setWebChromeClient(new WebChromeClient() {
                @Override
                public void onProgressChanged(WebView view, int newProgress) {
                    if (progressBar != null) {
                        progressBar.setProgress(newProgress);
                        if (newProgress >= 100) {
                            progressBar.setVisibility(View.GONE);
                        }
                    }
                }
            });

        } catch (Exception e) {
            Toast.makeText(this, e.toString(), Toast.LENGTH_LONG).show();
        }
    }

    private void loadWebsite() {
        if (currentUrl != null && !currentUrl.isEmpty()) {
            webView.loadUrl(currentUrl);
        } else {
            webView.loadUrl("https://silentmultipanel.vippanel.in");
        }
    }

    private void fetchAnnouncements() {
        ConfigManager.fetchConfig(this, new ConfigManager.ConfigCallback() {
            @Override
            public void onConfigLoaded(Config config) {
                // Check if server is enabled
                if (!config.isServerEnabled()) {
                    runOnUiThread(new Runnable() {
                        @Override
                        public void run() {
                            AlertDialog.Builder builder = new AlertDialog.Builder(MainActivity.this);
                            builder.setTitle("Server Offline");
                            builder.setMessage("The server has been turned off.");
                            builder.setPositiveButton("OK", (dialog, which) -> {
                                finish();
                            });
                            builder.setCancelable(false);
                            builder.show();
                        }
                    });
                }

                // Show announcement if available
                if (config.getAnnouncement() != null && !config.getAnnouncement().isEmpty()) {
                    runOnUiThread(new Runnable() {
                        @Override
                        public void run() {
                            AlertDialog.Builder builder = new AlertDialog.Builder(MainActivity.this);
                            builder.setTitle("Announcement");
                            builder.setMessage(config.getAnnouncement());
                            builder.setPositiveButton("OK", null);
                            builder.show();
                        }
                    });
                }
            }

            @Override
            public void onConfigFailed(String error) {
                // Silently fail - app should still work with fallback
            }
        });
    }

    private void autoFillIfSaved() {
        final String u = loginPrefs.getString(siteKey + "_user", null);
        final String p = loginPrefs.getString(siteKey + "_pass", null);
        if (u == null || p == null) return;

        String js =
                "(function(){" +
                        "var i=document.querySelector('input[type=text],input[type=email]');" +
                        "var j=document.querySelector('input[type=password]');" +
                        "if(i&&j){i.value='"+u+"';j.value='"+p+"';}" +
                        "})();";

        webView.evaluateJavascript(js, null);
    }

    private void captureIfTyped() {
        webView.evaluateJavascript(
                "(function(){" +
                        "var i=document.querySelector('input[type=text],input[type=email]');" +
                        "var j=document.querySelector('input[type=password]');" +
                        "if(i&&j&&i.value&&j.value){return i.value+'||'+j.value;}return '';})();",
                new ValueCallback<String>() {
                    @Override
                    public void onReceiveValue(String value) {
                        if (value == null) return;
                        value = value.replace("\"", "");
                        if (value.contains("||")) {
                            String[] v = value.split("\\|\\|");
                            if (v.length == 2) {
                                loginPrefs.edit()
                                        .putString(siteKey + "_user", v[0])
                                        .putString(siteKey + "_pass", v[1])
                                        .apply();
                            }
                        }
                    }
                }
        );
    }

    @Override
    public void onBackPressed() {
        if (webView != null && webView.canGoBack()) webView.goBack();
        else super.onBackPressed();
    }

    @Override
    protected void onPause() {
        super.onPause();
        if (webView != null) webView.onPause();
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (webView != null) webView.onResume();
    }

    @Override
    protected void onDestroy() {
        if (webView != null) {
            webView.clearHistory();
            webView.clearCache(true);
            webView.destroy();
        }
        super.onDestroy();
    }
}
