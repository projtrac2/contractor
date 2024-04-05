     <div class="m-footer">
       <p>ProjTrac M&E - Your Best Result-Based Monitoring & Evaluation System .</p>
       <p>Copyright @ 2017 - <?= date('Y') ?>. ProjTrac Systems Ltd .</p>
     </div>
     <?php
      if (isset($_SESSION["errorMessage"])) {
      ?>
       <div style="position:absolute; bottom: 12vh; right: 2vw; width: 35%;">
         <div class="m-alert">
           <i class="fa-solid fa-circle-exclamation" style="font-size: 26px; color: #dc2626; padding-left: 1vw"></i>
           <div>
             <p style="margin: 0px; font-size: 1rem; line-height: 1.5rem; font-weight: bold; letter-spacing: 1px; color: #7f1d1d;">Danger Alert</p>
             <p style="margin: 0px; font-size: 0.875rem; line-height: 1.25rem; letter-spacing: 0.6px;"><?= $_SESSION["errorMessage"] ?></p>
           </div>
         </div>
       </div>
     <?php
      }
      unset($_SESSION["errorMessage"]);

      if (isset($_SESSION["successMessage"])) {
      ?>
       <div style="position:absolute; bottom: 12vh; right: 2vw; width: 35%;">
         <div class="m-alert-danger">
           <i class="fa-solid fa-circle-check" style="font-size: 26px; color: #16a34a; padding-left: 1vw"></i>
           <div>
             <p style="margin: 0px; font-size: 1rem; line-height: 1.5rem; font-weight: bold; letter-spacing: 1px; color: #052e16;">Success Alert</p>
             <p style="margin: 0px; font-size: 0.875rem; line-height: 1.25rem; letter-spacing: 0.6px;"><?= $_SESSION["successMessage"] ?></p>
           </div>
         </div>
       </div>
     <?php
      }
      unset($_SESSION["successMessage"]);
      ?>

     <script src="assets/js/auth/auth.js"></script>
     </body>

     </html>