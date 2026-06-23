<?php
        $currentYear = $db->getCurrentTutorYear();
        $announcements = $db->getStudentAnnouncements($currentYear);
        ?>
        <div class="container-fluid mt-5 mb-5">
            <h2 class="mb-4 text-primary border-bottom pb-2"><i class="fa fa-bullhorn"></i> Πίνακας Ανακοινώσεων</h2>
            <?php if (empty($announcements)): ?>
                <div class="alert alert-info shadow-sm"><i class="fa fa-info-circle"></i> Δεν υπάρχουν ανακοινώσεις για το τρέχον έτος.</div>
            <?php else: ?>
                <div class="row justify-content-center">
                    <?php
                    foreach ($announcements as $ann):
                        // Υπολογισμός για το αν η ανακοίνωση είναι "Νέα"
                        $is_new = false;
                        $ann_timestamp = strtotime($ann['created_at']);
                        if ($ann_timestamp > strtotime('-3 days')) {
                            $is_new = true;
                        }
                    ?>
                        <div class="col-12 col-lg-8 mb-5" id="ann-<?php echo $ann['id']; ?>">
                            <div class="card shadow-sm border-0" style="border-top: 4px solid #007bff;">
                                <?php if (!empty($ann['imagePath'])): ?>
                                    <img src="images/announcements/<?php echo htmlspecialchars($ann['imagePath']); ?>" class="card-img-top p-3" style="max-height:400px; object-fit:contain;">
                                <?php endif; ?>
                                <div class="card-body p-4">
                                    <h4 class="card-title fw-bold text-dark mb-3">
                                        <?php echo htmlspecialchars($ann['title']); ?>
                                        <?php if ($is_new): ?>
                                            <span class="badge bg-danger ms-2 shadow-sm">ΝΕΟ!</span>
                                        <?php endif; ?>
                                    </h4>
                                    <h6 class="card-subtitle mb-3 text-muted small"><i class="fa fa-clock-o"></i> <?php echo date('d/m/Y H:i', strtotime($ann['created_at'])); ?></h6>
                                    <div class="card-text text-dark" style="font-size: 1.05rem; line-height: 1.6;"><?php echo $ann['content']; ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php
