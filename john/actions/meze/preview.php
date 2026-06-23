<?php
switch ($action) {
        case 'previewMeze':
            if (isset($_GET['id'])) {
                $res = $db->getMezedakiById($_GET['id']);
                $meze = $res->fetch_assoc();
            ?>
                <!DOCTYPE html>
                <html lang="el">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <base href="../">
                    <title>Μεζεδάκι #<?php echo $meze['mezeNumber']; ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
                    <link rel="stylesheet" href="aepp.css?v=<?php echo @filemtime(__DIR__ . '/../aepp.css'); ?>">
                    <style>
                        @media print {
                            .preview-box {
                                border: none !important;
                                padding: 0 !important;
                                box-shadow: none !important;
                            }

                            .bg-light {
                                background-color: transparent !important;
                            }

                            .container {
                                padding: 0 !important;
                                max-width: 100% !important;
                            }

                            .shadow-sm,
                            .shadow {
                                box-shadow: none !important;
                            }

                            .border {
                                border: 1px solid #dee2e6 !important;
                            }

                            .html-content-wrapper {
                                padding: 0 !important;
                                border: none !important;
                            }

                            h4 {
                                page-break-after: avoid;
                            }
                        }
                    </style>
                </head>

                <body class="p-0 p-md-4 bg-light">
                    <!-- Control Panel Εκτύπωσης -->
                    <div class="container d-print-none mb-4">
                        <div class="card shadow border-info">
                            <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                                <div class="mb-2 mb-md-0">
                                    <strong class="text-info me-3"><i class="fa fa-print"></i> Επιλογές Εκτύπωσης:</strong>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="chkQuestion" checked onchange="document.getElementById('questionBlock').classList.toggle('d-none', !this.checked)">
                                        <label class="form-check-label fw-bold" for="chkQuestion">Εκφώνηση</label>
                                    </div>
                                    <?php if (!empty($meze['mezeHints'])): ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="chkHints" checked onchange="document.getElementById('hintsBlock').classList.toggle('d-none', !this.checked)">
                                            <label class="form-check-label fw-bold" for="chkHints">Υποδείξεις</label>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="chkSolution" checked onchange="document.getElementById('solutionBlock').classList.toggle('d-none', !this.checked)">
                                        <label class="form-check-label fw-bold" for="chkSolution">Λύση</label>
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-info text-white shadow-sm fw-bold px-4" onclick="window.print()"><i class="fa fa-print"></i> Εκτύπωση</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container px-0 px-sm-3">
                        <div class="preview-box">
                            <h2 class="border-bottom pb-2 mb-4 text-dark font-weight-bold">
                                Μεζεδάκι #<?php echo $meze['mezeNumber']; ?>
                                <?php if (isset($meze['isSos']) && $meze['isSos'] == 1): ?>
                                    <span class="badge bg-danger ms-2" style="font-size: 1rem;"><i class="fa fa-fire"></i> SOS</span>
                                <?php endif; ?>
                            </h2>

                            <!-- Ενότητα Εκφώνησης -->
                            <div class="mb-5" id="questionBlock">
                                <h4 class="text-primary mb-3"><i class="fa fa-file-text-o"></i> Εκφώνηση</h4>
                                <?php if (!empty($meze['mezeImage'])): ?>
                                    <div class="text-center mb-3">
                                        <img src="images/mezedakia/<?php echo $meze['mezeImage']; ?>" class="img-fluid exam-img">
                                    </div>
                                <?php endif; ?>
                                <div class="html-content-wrapper p-3 border rounded bg-white">
                                    <?php echo $meze['mezeText']; ?>
                                </div>
                            </div>

                            <!-- Ενότητα Hints -->
                            <?php if (!empty($meze['mezeHints'])): ?>
                                <div class="mb-5" id="hintsBlock">
                                    <h4 class="text-info mb-3"><i class="fa fa-lightbulb-o"></i> Υποδείξεις / Hints</h4>
                                    <div class="alert alert-info border-info shadow-sm">
                                        <?php echo nl2br($meze['mezeHints']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Ενότητα Λύσης -->
                            <div class="mb-4" id="solutionBlock">
                                <h4 class="text-success mb-3"><i class="fa fa-check-circle"></i> Λύση</h4>
                                <div class="p-3 border border-success rounded bg-light shadow-sm">
                                    <?php if (!empty($meze['mezeSolutionImage'])): ?>
                                        <div class="text-center mb-3">
                                            <img src="images/mezedakia/<?php echo $meze['mezeSolutionImage']; ?>" class="img-fluid exam-img border-success">
                                        </div>
                                    <?php endif; ?>
                                    <div class="solution-text">
                                        <?php echo !empty($meze['mezeSolution']) ? $meze['mezeSolution'] : (empty($meze['mezeSolutionImage']) ? '<i class="text-muted">Δεν έχει καταχωρηθεί λύση.</i>' : ''); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Bootstrap 5 JS Bundle (Περιλαμβάνει Popper) -->
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                    <script>
                        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
                            let hintsCheck = document.getElementById('chkHints');
                            if (hintsCheck) {
                                hintsCheck.checked = false; // Απόκρυψη Hints από προεπιλογή στην αυτόματη εκτύπωση
                                document.getElementById('hintsBlock').classList.add('d-none');
                            }
                            setTimeout(() => window.print(), 800);
                        }
                    </script>
                </body>

                </html>
            <?php
                exit();
            }
            break;
}
