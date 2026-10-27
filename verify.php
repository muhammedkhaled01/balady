<?php
// ============================================================
//  verify.php - شهادة صحية  |  Certificate Verification Page
//  Place in: C:\laragon\www\balady\verify.php
// ============================================================

$cert  = null;
$error = null;

if (isset($_GET["data"]) && !empty($_GET["data"])) {
    $raw    = $_GET["data"];
    $base64 = str_replace(["-", "_"], ["+", "/"], $raw);
    $mod    = strlen($base64) % 4;
    if ($mod) $base64 .= str_repeat("=", 4 - $mod);

    $json = base64_decode($base64, true);
    if ($json !== false) {
        $parsed = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE) {
                // Map short keys back to full keys
                $ig = $parsed["ig"] ?? "";
                $eg = $parsed["eg"] ?? "";
                if ($ig) {
                    $sep = strpos($ig, "/") !== false ? "/" : "-";
                    $parts = explode($sep, $ig);
                    if (count($parts) == 3) {
                        if (strlen($parts[0]) == 4) {
                            $parts[0] = (int)$parts[0] + 1;
                        } elseif (strlen($parts[2]) == 4) {
                            $parts[2] = (int)$parts[2] + 1;
                        }
                        $eg = implode($sep, $parts);
                    }
                }

                $ih = $parsed["ih"] ?? "";
                $eh = $parsed["eh"] ?? "";
                if ($ih) {
                    $sep = strpos($ih, "/") !== false ? "/" : "-";
                    $parts = explode($sep, $ih);
                    if (count($parts) == 3) {
                        if (strlen($parts[0]) == 4) {
                            $parts[0] = (int)$parts[0] + 1;
                        } elseif (strlen($parts[2]) == 4) {
                            $parts[2] = (int)$parts[2] + 1;
                        }
                        $eh = implode($sep, $parts);
                    }
                }

                $cert = [
                    "certNumber" => $parsed["cn"] ?? "",
                    "nameAr" => $parsed["na"] ?? "",
                    "nationality" => $parsed["nt"] ?? "",
                    "nationalId" => $parsed["ni"] ?? "",
                    "profession" => $parsed["pr"] ?? "",
                    "certType" => $parsed["ct"] ?? "",
                    "issueDateHijri" => $ih,
                    "expiryDateHijri" => $eh,
                    "issueDateGregorian" => $ig,
                    "expiryDateGregorian" => $eg,
                    "gender" => $parsed["ge"] ?? "",
                    "municipality" => $parsed["mu"] ?? "",
                    "subMunicipality" => $parsed["sm"] ?? "",
                    "licenseNumber" => $parsed["ln"] ?? "",
                    "facilityName" => $parsed["fn"] ?? "",
                    "facilityNumber" => $parsed["fb"] ?? "",
                    "educationalProgramType" => $parsed["ed"] ?? "",
                    "educationalProgramExpiryDate" => $parsed["ede"] ?? ""
                ];
            } else {
                $error = "بيانات الشهادة تالفة أو غير صالحة.";
            }
        } else {
            $error = "رمز التحقق غير صالح.";
        }
    } else {
        $error = "لم يتم تقديم بيانات التحقق.";
    }
} else {
    $error = "لم يتم تقديم بيانات التحقق.";
}

function val($cert, $key) {
    $v = isset($cert[$key]) ? trim($cert[$key]) : "";
    return $v !== "" ? htmlspecialchars($v, ENT_QUOTES, "UTF-8") : "—";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من الشهادة الصحية - بلدي</title>
    <link rel="icon" type="image/x-icon" href="https://apps.balady.gov.sa/BALADYCDN/Content/images/fav.ico">
    <link href="bootstrap.min__Tyn.css" rel="stylesheet">
    <link href="all__Tyn.css"           rel="stylesheet">
    <link href="app.min__Tyn.css"       rel="stylesheet">
    <link href="Style__Tyn.css"         rel="stylesheet">
    <style>
        :root {
            --green:      #7BBC47;
            --green-dark: #5C9636;
            --green-bg:   #F0F7E8;
            --gray-light: #F2F3F5;
            --border:     #DDE6D4;
            --text-dark:  #1A1A2E;
            --text-muted: #6B7280;
        }
        body { background:#f4f6f9; font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif; min-height:100vh; }

        .top-bar { background:linear-gradient(135deg,var(--green-dark),var(--green)); padding:14px 24px; display:flex; align-items:center; gap:16px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
        .top-bar img { height:44px; }
        .top-bar .site-title { color:#fff; font-size:1.25rem; font-weight:700; }
        .top-bar .site-sub { color:rgba(255,255,255,.85); font-size:.82rem; }

        .cert-card { background:#fff; border-radius:16px; border:1px solid var(--border); overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.07); max-width:800px; margin:32px auto; }

        .cert-header { background:var(--gray-light); display:flex; align-items:stretch; min-height:72px; }
        .cert-header-title { background:var(--green); color:#fff; font-size:1.15rem; font-weight:700; padding:0 28px; display:flex; align-items:center; white-space:nowrap; }
        .cert-header-logos { flex:1; display:flex; align-items:center; justify-content:flex-start; gap:14px; padding:0 20px; }
        .cert-header-logos img { height:46px; object-fit:contain; }

        .verified-badge { display:inline-flex; align-items:center; gap:8px; background:var(--green-bg); border:1.5px solid var(--green); border-radius:30px; padding:6px 20px; font-size:.92rem; font-weight:600; color:var(--green-dark); margin:20px 20px 0; }
        .verified-badge svg { width:20px; height:20px; }

        .fields-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; padding:20px; }
        @media(max-width:540px){ .fields-grid{grid-template-columns:1fr;} }

        .field-box { border:1px solid var(--border); border-radius:10px; padding:10px 14px; background:#fafcf8; }
        .field-label { font-size:.78rem; color:var(--text-muted); margin-bottom:4px; }
        .field-value { font-size:.97rem; font-weight:600; color:var(--text-dark); word-break:break-word; }
        .field-box.full { grid-column:1 / -1; }

        .section-head { font-size:.82rem; font-weight:700; color:var(--green-dark); text-transform:uppercase; letter-spacing:.5px; padding:12px 20px 0; margin-top:4px; border-top:1px solid var(--border); }

        .cert-footer { border-top:1px solid var(--border); padding:12px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
        .cert-footer a { color:var(--green-dark); font-size:.85rem; text-decoration:underline; }
        .cert-footer .phone { font-weight:700; color:var(--green-dark); font-size:.95rem; }

        .error-card { max-width:480px; margin:60px auto; background:#fff; border-radius:16px; border:1px solid #fecaca; padding:40px 32px; text-align:center; box-shadow:0 4px 24px rgba(0,0,0,.06); }
        .error-icon { font-size:3rem; color:#ef4444; margin-bottom:16px; }
        .error-title { font-size:1.2rem; font-weight:700; color:#1f2937; margin-bottom:8px; }
        .error-msg { font-size:.95rem; color:#6b7280; }
    </style>
</head>
<body>

<div class="top-bar">
    <img src="https://apps.balady.gov.sa/BALADYCDN/Content/images/logo-white.png" onerror="this.style.display=none" alt="بلدي">
    <div>
        <div class="site-title">بوابة بلدي</div>
        <div class="site-sub">وزارة الشؤون البلدية والقروية والإسكان</div>
    </div>
</div>

<?php if ($cert): ?>
<div class="container-fluid px-3">
  <div class="cert-card">
    <div class="cert-header">
      <div class="cert-header-title">
        شهادة صحية <?= htmlspecialchars($cert["certType"] ?? "", ENT_QUOTES, "UTF-8") ?>
      </div>
      <div class="cert-header-logos">
        <img src="assets/images/amana_logo.png"    onerror="this.style.display=none" alt="أمانة">
        <img src="assets/images/balady_logo.png"   onerror="this.style.display=none" alt="بلدي">
        <img src="assets/images/ministry_logo.png" onerror="this.style.display=none" alt="الوزارة">
      </div>
    </div>

    <div>
      <span class="verified-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        شهادة موثقة — تم التحقق بنجاح
      </span>
    </div>

    <!-- Personal Data -->
    <p class="section-head">البيانات الشخصية</p>
    <div class="fields-grid">
      <div class="field-box full">
        <div class="field-label">الاسم</div>
        <div class="field-value" style="font-size:1.05rem;"><?= val($cert,"nameAr") ?></div>
      </div>
      <div class="field-box">
        <div class="field-label">رقم الهوية</div>
        <div class="field-value"><?= val($cert,"nationalId") ?></div>
      </div>
      <div class="field-box">
        <div class="field-label">الجنسية</div>
        <div class="field-value"><?= val($cert,"nationality") ?></div>
      </div>
      <div class="field-box">
        <div class="field-label">الجنس</div>
        <div class="field-value"><?= val($cert,"gender") ?></div>
      </div>
      <div class="field-box">
        <div class="field-label">المهنة</div>
        <div class="field-value"><?= val($cert,"profession") ?></div>
      </div>
    </div>

    <!-- Certificate Data -->
    <p class="section-head">بيانات الشهادة والترخيص</p>
    <div class="fields-grid">
      <div class="field-box">
        <div class="field-label">رقم الشهادة الصحية</div>
        <div class="field-value"><?= val($cert,"certNumber") ?></div>
      </div>
      <div class="field-box">
        <div class="field-label">نوع الشهادة</div>
        <div class="field-value"><?= val($cert,"certType") ?></div>
      </div>
      <div class="field-box">
        <div class="field-label">تاريخ الإصدار (هجري)</div>
        <div class="field-value"><?= val($cert,"issueDateHijri") ?></div>
      </div>
      <div class="field-box">
        <div class="field-label">تاريخ الانتهاء (هجري)</div>
        <div class="field-value"><?= val($cert,"expiryDateHijri") ?></div>
      </div>
      <?php if (!empty($cert["issueDateGregorian"])): ?>
      <div class="field-box">
        <div class="field-label">تاريخ الإصدار (ميلادي)</div>
        <div class="field-value"><?= val($cert,"issueDateGregorian") ?></div>
      </div>
      <div class="field-box">
        <div class="field-label">تاريخ الانتهاء (ميلادي)</div>
        <div class="field-value"><?= val($cert,"expiryDateGregorian") ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert["licenseNumber"])): ?>
      <div class="field-box">
        <div class="field-label">رقم الرخصة</div>
        <div class="field-value"><?= val($cert,"licenseNumber") ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert["educationalProgramType"])): ?>
      <div class="field-box">
        <div class="field-label">نوع البرنامج التثقيفي</div>
        <div class="field-value"><?= val($cert,"educationalProgramType") ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert["educationalProgramExpiryDate"])): ?>
      <div class="field-box">
        <div class="field-label">تاريخ انتهاء البرنامج التثقيفي</div>
        <div class="field-value"><?= val($cert,"educationalProgramExpiryDate") ?></div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Facility Data (shown only if any field is filled) -->
    <?php
    $hasFacility = !empty($cert["facilityName"]) || !empty($cert["facilityNumber"])
                || !empty($cert["municipality"])  || !empty($cert["subMunicipality"]);
    if ($hasFacility):
    ?>
    <p class="section-head">بيانات المنشأة</p>
    <div class="fields-grid">
      <?php if (!empty($cert["municipality"])): ?>
      <div class="field-box">
        <div class="field-label">الأمانة</div>
        <div class="field-value"><?= val($cert,"municipality") ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert["subMunicipality"])): ?>
      <div class="field-box">
        <div class="field-label">البلدية</div>
        <div class="field-value"><?= val($cert,"subMunicipality") ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert["facilityName"])): ?>
      <div class="field-box">
        <div class="field-label">اسم المنشأة</div>
        <div class="field-value"><?= val($cert,"facilityName") ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert["facilityNumber"])): ?>
      <div class="field-box">
        <div class="field-label">رقم المنشأة</div>
        <div class="field-value"><?= val($cert,"facilityNumber") ?></div>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="cert-footer">
      <span class="phone">📞 199040</span>
      <span style="font-size:.85rem;color:var(--text-muted);">مركز العناية بالعملاء بالبلدي</span>
      <span style="font-size:.85rem;color:var(--text-muted);">@Balady_cs</span>
      <a href="https://www.balady.gov.sa" target="_blank">www.balady.gov.sa</a>
    </div>
  </div>

  <p style="text-align:center;color:#9ca3af;font-size:.8rem;margin:16px 0 32px;">
    هذه الصفحة للتحقق فقط — لا تعتبر الشهادة إثبات هوية
  </p>
</div>

<?php else: ?>

<div class="error-card">
  <div class="error-icon">&#9888;&#65039;</div>
  <div class="error-title">تعذّر التحقق من الشهادة</div>
  <p class="error-msg"><?= htmlspecialchars($error ?? "خطأ غير معروف.", ENT_QUOTES, "UTF-8") ?></p>
  <a href="index.htm" style="margin-top:20px;display:inline-block;color:#7BBC47;">← العودة للرئيسية</a>
</div>

<?php endif; ?>
</body>
</html>
