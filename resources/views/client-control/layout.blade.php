<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#12344b">
<title>@yield('title', 'Client Portal') | Ravion</title>
<style>
:root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,'Segoe UI',sans-serif;color:#19344b;background:#f4f7fb;font-synthesis:none}
*{box-sizing:border-box}body{margin:0}a{color:inherit;text-decoration:none}button{font:inherit;cursor:pointer}
.topbar{background:#fff;border-bottom:1px solid #e1e9f0}.top-inner{max-width:1180px;margin:auto;min-height:74px;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.brand{font-size:20px;font-weight:800;letter-spacing:-.6px;color:#153c55}.brand span{display:block;font-size:11px;letter-spacing:.8px;font-weight:650;color:#74879a;text-transform:uppercase}
.header-right{display:flex;align-items:center;gap:14px}.client-name{font-size:13px;color:#64788c}.signout{border:1px solid #d7e2eb;background:#fff;border-radius:9px;padding:9px 14px;color:#244b63}
.container{max-width:1180px;margin:0 auto;padding:28px 22px 70px}.eyebrow{font-size:12px;text-transform:uppercase;letter-spacing:1.3px;color:#64829a;font-weight:750}.page-title{font-size:clamp(26px,4vw,36px);letter-spacing:-1px;margin:7px 0}.lead{color:#6a7e90;line-height:1.6;margin:0 0 24px}
.card{background:#fff;border:1px solid #e0e8f0;border-radius:16px;padding:24px;box-shadow:0 8px 26px rgba(19,50,75,.035)}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px}.project-card{display:block;transition:border-color .15s,transform .15s}.project-card:hover{border-color:#8daec4;transform:translateY(-2px)}.project-name{font-size:19px;font-weight:750;margin:8px 0}.muted{color:#73869a;font-size:13px}.tag{display:inline-block;background:#eaf3f8;color:#315e77;border-radius:7px;padding:6px 9px;font-size:12px;font-weight:700}
.project-header{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap}.back{display:inline-block;margin-bottom:18px;font-size:13px;color:#416983}.nav{display:flex;gap:6px;overflow-x:auto;padding:8px 0 12px;margin:15px 0 22px;border-bottom:1px solid #dde7ef;scrollbar-width:thin}.nav-item{white-space:nowrap;padding:10px 14px;border-radius:9px;color:#526b80;font-size:13px;font-weight:650}.nav-item.active{background:#153c55;color:white}.nav-item.disabled{opacity:.55;cursor:not-allowed}.section-title{font-size:18px;margin:0 0 10px}.empty{padding:18px 0;color:#6d8092;font-size:14px;line-height:1.6}.status{display:inline-block;padding:6px 10px;background:#f0f4f8;color:#536a7c;border-radius:7px;font-size:12px;font-weight:700}.footer{font-size:12px;color:#8090a0;margin-top:35px}
@media(max-width:640px){.top-inner{padding:12px 16px;min-height:66px}.client-name{display:none}.container{padding:22px 16px 60px}.card{padding:19px}.nav{margin-left:-16px;margin-right:-16px;padding-left:16px;padding-right:16px}.nav-item{padding:10px 12px}}
.rv-brand-symbol{flex:none}.brand .rv-brand-wordmark{display:flex;flex-direction:column;font-size:22px;line-height:1.05;letter-spacing:-.7px;font-weight:850;color:#112957}.brand .rv-brand-wordmark small{font-size:10px;letter-spacing:3px;font-weight:750;margin-top:3px}.brand .rv-brand-portal{display:flex;flex-direction:column;font-size:14px;letter-spacing:0;text-transform:none;font-weight:800;color:#183052;border-left:1px solid #ccdaed;padding-left:18px;margin-left:6px}.brand .rv-brand-portal small{font-size:11px;letter-spacing:0;font-weight:500;text-transform:none;color:#59708d;margin-top:3px}@media(max-width:540px){.brand .rv-brand-portal{display:none}.brand .rv-brand-wordmark{font-size:18px}}

/* Shared project navigation: one source of truth across all project screens. */
body:has(.rv-project-navigation){background:#f1f6ff;color:#102448}
body:has(.rv-project-navigation) .container{max-width:1600px;padding:16px 30px 65px}
body:has(.rv-project-navigation) .topbar{background:linear-gradient(100deg,#fff 0%,#edf5ff 100%);border-bottom:1px solid #dce7f6}
body:has(.rv-project-navigation) .top-inner{max-width:1600px;padding:12px 30px;min-height:68px}
body:has(.rv-project-navigation) .brand{display:flex;align-items:center;gap:12px;font-size:24px;letter-spacing:-.9px;color:#10264e}
body:has(.rv-project-navigation) .signout{border-radius:10px;color:#163d65;border-color:#d7e5f7}
.rv-project-navigation{display:flex;align-items:center;justify-content:space-between;gap:10px;overflow-x:auto;margin:10px 0 14px;padding:5px 15px 8px;border:0;border-radius:14px;scrollbar-width:thin}
.rv-project-navigation .rv-project-link{display:flex;align-items:center;justify-content:center;white-space:nowrap;padding:10px 15px;border-radius:22px;color:#294977;font-size:13px;font-weight:700;text-decoration:none;flex:none;min-height:37px}
.rv-project-navigation .rv-project-link:hover:not(.is-active){background:#e3edfa;color:#123c70}
.rv-project-navigation .rv-project-link.is-active{background:linear-gradient(135deg,#245e93,#0f315d);color:#fff;box-shadow:0 4px 9px #17456b2e}
.rv-project-navigation .rv-project-link.is-disabled{color:#8ba0bd;opacity:.75;cursor:not-allowed}
.rv-project-navigation .rv-project-link:focus-visible{outline:3px solid #0e8897;outline-offset:2px}
@media(max-width:800px){body:has(.rv-project-navigation) .container{padding:12px 16px 50px};body:has(.rv-project-navigation) .top-inner{padding:12px 16px}}
@media(max-width:580px){.rv-project-navigation{justify-content:flex-start;margin-left:0;margin-right:0;padding-left:0;padding-right:0}}
</style>
</head>
<body>
<header class="topbar"><div class="top-inner"><a class="brand" href="{{ route('client.projects.index') }}" aria-label="Ravion Homes Client Portal"><svg class="rv-brand-symbol" viewBox="0 0 46 45" width="38" height="38" aria-hidden="true"><path d="M4 39V22l9-7v24M18 39V12l9-7v34M32 39V18l10 7v14" fill="none" stroke="#163e72" stroke-width="5" stroke-linejoin="round"/><path d="M26 5v34" stroke="#e4a92c" stroke-width="4"/></svg><span class="rv-brand-wordmark">RAVION<small>H O M E S</small></span><span class="rv-brand-portal">Client Portal<small>Your Project Workspace</small></span></a><div class="header-right"><span class="client-name">{{ auth('client')->user()?->name }}</span><form method="POST" action="{{ route('client.logout') }}">@csrf<button class="signout" type="submit">Sign out</button></form></div></div></header>
<main class="container">@yield('content')<div class="footer">Ravion Client Portal · Verified and approved information only</div></main>
</body></html>
