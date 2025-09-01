<?php
/*
|--------------------------------------------------------------------------
| ส่วนหัวไฟล์ (Thai Doc Block)
|--------------------------------------------------------------------------
| ไฟล์นี้รับผิดชอบ: แบบประเมินความเสี่ยงไซเบอร์และ Incident Response Plan
| Flow: รับ input จากผู้ใช้, ประเมินความเสี่ยง, แสดงผล, ส่งออกไฟล์
| Input: responses จากแบบฟอร์ม
| Output: UI, export PDF/CSV/JSON
| Error: ตรวจสอบ null, handle export error, validate input
| ส่วนประกอบ: ฟอร์ม, ผลลัพธ์, IRP, export, modal
*/
?>
<?php
// ---------------------------
// ส่วน PHP: กำหนดชุดคำถามสำหรับประเมินความเสี่ยง
// ---------------------------
$questions = [
  [
    "section" => "การจัดการข้อมูล", // เพิ่มหมวด
    "icon" => "🗂️",
    "text" => "มีรายการระบบ/ข้อมูลสำคัญ ระบุชัดเจนหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "หากไม่มีการระบุข้อมูลสำคัญ อาจทำให้ขาดการควบคุมและเสี่ยงต่อการรั่วไหลหรือสูญหายของข้อมูลสำคัญ",
    "fix" => "จัดทำรายการข้อมูลและระบบสำคัญให้ชัดเจน",
    "steps" => [
      "สำรวจและระบุข้อมูล/ระบบที่สำคัญขององค์กร",
      "จัดทำเอกสารหรือ inventory",
      "ทบทวนรายการเป็นประจำ"
    ]
  ],
  [
    "section" => "การจัดการข้อมูล",
    "icon" => "🗂️",
    "text" => "ข้อมูลสำคัญถูกจัดระดับความลับหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลสำคัญอาจถูกเข้าถึงหรือเปิดเผยโดยไม่ได้รับอนุญาต",
    "fix" => "กำหนดระดับความลับของข้อมูลแต่ละประเภท",
    "steps" => [
      "กำหนด policy การจัดระดับข้อมูล",
      "ติดป้ายหรือกำหนด label ให้ข้อมูล",
      "อบรมพนักงานเรื่องการจัดการข้อมูล"
    ]
  ],
  [
    "section" => "การควบคุมสิทธิ์",
    "icon" => "🔑",
    "text" => "การเข้าถึงไฟล์/โฟลเดอร์สำคัญจำกัดเฉพาะผู้ที่จำเป็นหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลสำคัญอาจถูกเข้าถึงโดยผู้ไม่มีสิทธิ์ เสี่ยงต่อการรั่วไหลหรือแก้ไขข้อมูล",
    "fix" => "จำกัดสิทธิ์การเข้าถึงเฉพาะผู้ที่จำเป็น",
    "steps" => [
      "ตรวจสอบสิทธิ์การเข้าถึงไฟล์/โฟลเดอร์",
      "ปรับสิทธิ์ให้เหมาะสม",
      "ทบทวนสิทธิ์เป็นประจำ"
    ]
  ],
  [
    "section" => "การควบคุมสิทธิ์",
    "icon" => "🔑",
    "text" => "เปิดใช้ 2FA/MFA กับบัญชีสำคัญหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "บัญชีอาจถูกแฮกได้ง่ายขึ้นหากไม่มี 2FA/MFA",
    "fix" => "เปิดใช้งาน 2FA/MFA สำหรับบัญชีสำคัญ",
    "steps" => [
      "เลือกวิธี 2FA/MFA ที่เหมาะสม",
      "ตั้งค่าในระบบ/บัญชี",
      "แจ้งและอบรมผู้ใช้"
    ]
  ],
  [
    "text" => "ใช้หลักการสิทธิ์เท่าที่จำเป็น (Least Privilege) หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ผู้ใช้หรือระบบอาจมีสิทธิ์เกินความจำเป็น เสี่ยงต่อการถูกโจมตีหรือใช้งานผิดวัตถุประสงค์",
    "fix" => "กำหนดสิทธิ์เฉพาะที่จำเป็นต่อหน้าที่",
    "steps" => [
      "ทบทวนสิทธิ์ของผู้ใช้และระบบ",
      "ปรับลดสิทธิ์ที่ไม่จำเป็น",
      "ตรวจสอบเป็นระยะ"
    ]
  ],
  [
    "text" => "แยกบัญชี Admin ออกจากบัญชีใช้งานปกติ และทบทวนสิทธิ์เป็นระยะหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "บัญชี admin ถูกใช้ในงานประจำวัน เสี่ยงต่อการถูกโจมตีและขยายผล",
    "fix" => "แยกบัญชี admin และบัญชีใช้งานทั่วไป",
    "steps" => [
      "สร้างบัญชี admin แยกจาก user ปกติ",
      "ใช้บัญชี admin เฉพาะเวลาจำเป็น",
      "ทบทวนสิทธิ์ admin เป็นประจำ"
    ]
  ],
  [
    "text" => "มีขั้นตอนรับเข้า/ย้ายงาน/ลาออก ที่ปรับ/ปิดสิทธิ์ทันทีหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "อดีตพนักงานหรือผู้ย้ายงานอาจยังเข้าถึงระบบได้",
    "fix" => "กำหนดขั้นตอนปิดสิทธิ์เมื่อมีการเปลี่ยนแปลงสถานะ",
    "steps" => [
      "แจ้ง HR/IT เมื่อมีการเปลี่ยนแปลง",
      "ปิด/ปรับสิทธิ์ทันที",
      "ตรวจสอบสิทธิ์หลังการเปลี่ยนแปลง"
    ]
  ],
  [
    "section" => "การอัปเดต/ช่องโหว่",
    "icon" => "🛡️",
    "text" => "อัปเดตแพตช์ความปลอดภัยตรงเวลา (เช่น ช่องโหว่สำคัญภายใน 14 วัน) หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ระบบอาจถูกโจมตีผ่านช่องโหว่ที่ยังไม่ได้อัปเดต",
    "fix" => "อัปเดตแพตช์ความปลอดภัยตามรอบเวลา",
    "steps" => [
      "ติดตามประกาศช่องโหว่",
      "วางแผนและดำเนินการอัปเดต",
      "ทดสอบระบบหลังอัปเดต"
    ]
  ],
  [
    "text" => "มีการสแกนช่องโหว่ระบบสำคัญเป็นประจำหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ช่องโหว่ที่ยังไม่ถูกค้นพบอาจถูกใช้โจมตี",
    "fix" => "สแกนช่องโหว่ระบบสำคัญอย่างสม่ำเสมอ",
    "steps" => [
      "เลือกเครื่องมือสแกนช่องโหว่",
      "กำหนดรอบการสแกน",
      "แก้ไขช่องโหว่ที่พบ"
    ]
  ],
  [
    "section" => "การสำรองข้อมูล",
    "icon" => "💾",
    "text" => "มี Asset Inventory ที่อัปเดตเสมอหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ไม่ทราบว่ามีอุปกรณ์หรือระบบใดบ้าง เสี่ยงต่อการควบคุมและป้องกัน",
    "fix" => "จัดทำและอัปเดต Asset Inventory",
    "steps" => [
      "รวบรวมข้อมูลอุปกรณ์/ระบบทั้งหมด",
      "บันทึกในระบบหรือเอกสาร",
      "อัปเดตเมื่อมีการเปลี่ยนแปลง"
    ]
  ],
  [
    "text" => "สำรองข้อมูลของระบบ/ไฟล์สำคัญตามรอบเวลาหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลอาจสูญหายถ้าไม่มีการสำรองข้อมูล",
    "fix" => "สำรองข้อมูลตามรอบเวลาที่กำหนด",
    "steps" => [
      "กำหนดรอบเวลาสำรองข้อมูล",
      "เลือกวิธีสำรองที่เหมาะสม",
      "ทดสอบการกู้คืนข้อมูล"
    ]
  ],
  [
    "text" => "มีสำรองแบบ Offline/Immutable/Offsite เพื่อกัน Ransomware หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลสำรองอาจถูกโจมตีพร้อมกับข้อมูลหลัก",
    "fix" => "ใช้การสำรองแบบ Offline/Immutable/Offsite",
    "steps" => [
      "กำหนดนโยบายสำรองข้อมูล",
      "เลือกวิธีสำรองที่ปลอดภัย",
      "ทดสอบการกู้คืนจากสำรอง"
    ]
  ],
  [
    "section" => "การตรวจจับเหตุการณ์",
    "icon" => "🔎",
    "text" => "กำหนด RTO/RPO แบบเข้าใจง่ายไว้หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ไม่ทราบระยะเวลาที่ต้องการกู้คืนระบบ/ข้อมูล เสี่ยงต่อการฟื้นฟูไม่ทันเวลา",
    "fix" => "กำหนด RTO/RPO ให้ชัดเจน",
    "steps" => [
      "วิเคราะห์ความต้องการธุรกิจ",
      "กำหนด RTO/RPO",
      "แจ้งให้ทีมงานรับทราบ"
    ]
  ],
  [
    "text" => "เคยทดสอบกู้คืนจริงใน 6–12 เดือนที่ผ่านมาและบันทึกผลหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "อาจกู้คืนข้อมูลไม่ได้จริงเมื่อเกิดเหตุ",
    "fix" => "ทดสอบการกู้คืนข้อมูลเป็นประจำ",
    "steps" => [
      "วางแผนทดสอบการกู้คืน",
      "ดำเนินการทดสอบ",
      "บันทึกผลและปรับปรุง"
    ]
  ],
  [
    "section" => "การตอบสนองเหตุการณ์",
    "icon" => "🚨",
    "text" => "รวบรวม Logs ไว้ที่จุดกลาง (เช่น Syslog/SIEM) หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูล Log อาจกระจัดกระจาย ตรวจสอบย้อนหลังได้ยาก",
    "fix" => "รวบรวม Log ไว้ที่จุดกลาง",
    "steps" => [
      "เลือกโซลูชัน Log Centralization",
      "ตั้งค่าระบบให้ส่ง Log",
      "ตรวจสอบ Log เป็นประจำ"
    ]
  ],
  [
    "text" => "มีระบบแจ้งเตือนเหตุผิดปกติและมีคนเฝ้าดูในเวลางานหรือ 24/7 หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เหตุผิดปกติอาจถูกตรวจพบล่าช้า",
    "fix" => "ตั้งค่าระบบแจ้งเตือนและมีผู้รับผิดชอบ",
    "steps" => [
      "กำหนดเหตุการณ์ที่ต้องแจ้งเตือน",
      "ตั้งค่าระบบแจ้งเตือน",
      "มอบหมายผู้รับผิดชอบ"
    ]
  ],
  [
    "section" => "การป้องกันมัลแวร์",
    "icon" => "🦠",
    "text" => "ทุกเครื่องมี Antivirus/EDR และอัปเดตอัตโนมัติหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เครื่องอาจติดมัลแวร์หากไม่มีการป้องกันหรืออัปเดต",
    "fix" => "ติดตั้ง Antivirus/EDR และตั้งค่าอัปเดตอัตโนมัติ",
    "steps" => [
      "เลือกโซลูชัน Antivirus/EDR",
      "ติดตั้งในทุกเครื่อง",
      "ตั้งค่าให้อัปเดตอัตโนมัติ"
    ]
  ],
  [
    "section" => "การบริหารจัดการ",
    "icon" => "👔",
    "text" => "มีแผน IRP เป็นลายลักษณ์อักษรและอัปเดตไม่เกิน 12 เดือนหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ขาดแผนรับมือเหตุการณ์ อาจตอบสนองล่าช้าเมื่อเกิดเหตุ",
    "fix" => "จัดทำและอัปเดตแผน IRP",
    "steps" => [
      "เขียนแผน IRP ให้ครบถ้วน",
      "ทบทวนและอัปเดตทุกปี",
      "แจ้งให้ทีมงานรับทราบ"
    ]
  ],
  [
    "text" => "มีรายชื่อผู้รับผิดชอบ/เบอร์ติดต่อฉุกเฉิน (ใน–นอกองค์กร) ที่เข้าถึงได้เร็วหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ติดต่อผู้เกี่ยวข้องไม่ได้ทันทีเมื่อเกิดเหตุ",
    "fix" => "จัดทำรายชื่อและช่องทางติดต่อฉุกเฉิน",
    "steps" => [
      "รวบรวมรายชื่อและเบอร์ติดต่อ",
      "จัดเก็บในที่เข้าถึงง่าย",
      "ทบทวนและอัปเดตข้อมูล"
    ]
  ],
  [
    "text" => "มี Playbook อย่างน้อยฟิชชิง/มัลแวร์/แรนซัมแวร์หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ขาดแนวทางปฏิบัติเมื่อเกิดเหตุเฉพาะหน้า",
    "fix" => "จัดทำ Playbook สำหรับเหตุการณ์สำคัญ",
    "steps" => [
      "ระบุเหตุการณ์ที่พบบ่อย",
      "เขียนขั้นตอนรับมือแต่ละเหตุการณ์",
      "อบรมทีมงาน"
    ]
  ],
  [
    "text" => "มีขั้นตอนควบคุมเหตุไม่ให้ลาม และเคยซ้อมแล้วหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เหตุการณ์อาจลุกลามขยายวงกว้าง",
    "fix" => "กำหนดขั้นตอนควบคุมเหตุและซ้อมรับมือ",
    "steps" => [
      "วางแผนขั้นตอนควบคุมเหตุ",
      "ซ้อมรับมือกับทีมงาน",
      "ปรับปรุงขั้นตอนตามผลซ้อม"
    ]
  ],
  [
    "text" => "เคยซ้อมสถานการณ์ปีละครั้ง และสรุปบทเรียน/ปรับแผนหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ทีมงานอาจไม่พร้อมรับมือเหตุการณ์จริง",
    "fix" => "ซ้อมสถานการณ์และสรุปบทเรียนทุกปี",
    "steps" => [
      "วางแผนซ้อมสถานการณ์",
      "ดำเนินการซ้อม",
      "สรุปบทเรียนและปรับแผน"
    ]
  ],
  [
    "section" => "การปฏิบัติตามกฎหมาย",
    "icon" => "📜",
    "text" => "แผน BCP/DR สอดคล้องกับ IRP และเคยทดสอบหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "แผน BCP/DR ไม่สอดคล้องกับ IRP อาจฟื้นฟูระบบไม่ได้ตามเป้าหมาย",
    "fix" => "ทบทวนและทดสอบแผน BCP/DR ให้สอดคล้องกับ IRP",
    "steps" => [
      "เปรียบเทียบแผน BCP/DR กับ IRP",
      "ปรับปรุงให้สอดคล้องกัน",
      "ทดสอบแผนเป็นประจำ"
    ]
  ],
  [
    "text" => "ทราบ PDPA/ข้อกำหนดลูกค้า และขั้นตอนการแจ้งเหตุหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "อาจละเมิดกฎหมายหรือข้อกำหนดลูกค้าเมื่อเกิดเหตุ",
    "fix" => "ศึกษาข้อกำหนดและกำหนดขั้นตอนแจ้งเหตุ",
    "steps" => [
      "ศึกษากฎหมาย/ข้อกำหนดที่เกี่ยวข้อง",
      "กำหนดขั้นตอนแจ้งเหตุ",
      "อบรมทีมงาน"
    ]
  ],
  [
    "text" => "มีช่องทางติดต่อ CERT/ผู้เชี่ยวชาญภายนอกไว้ล่วงหน้าหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ไม่สามารถขอความช่วยเหลือจากผู้เชี่ยวชาญได้ทันที",
    "fix" => "เตรียมช่องทางติดต่อ CERT/ผู้เชี่ยวชาญไว้ล่วงหน้า",
    "steps" => [
      "ค้นหาข้อมูล CERT/ผู้เชี่ยวชาญ",
      "บันทึกช่องทางติดต่อ",
      "ทดสอบการติดต่อ"
    ]
  ],
  [
    "section" => "โครงสร้างพื้นฐาน",
    "icon" => "🌐",
    "text" => "เครือข่ายแบ่งโซน/เซกเมนต์ลดการลามของเหตุหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เหตุการณ์อาจลุกลามไปยังระบบอื่นได้ง่าย",
    "fix" => "แบ่งโซนเครือข่ายเพื่อลดการลาม",
    "steps" => [
      "วิเคราะห์โครงสร้างเครือข่าย",
      "กำหนดโซน/segment",
      "ตั้งค่า firewall ระหว่างโซน"
    ]
  ],
  [
    "text" => "ทบทวนกฎไฟร์วอลล์ ปิดพอร์ต/บริการที่ไม่จำเป็นเป็นระยะหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เปิดพอร์ต/บริการที่ไม่จำเป็น เสี่ยงต่อการถูกโจมตี",
    "fix" => "ทบทวนและปิดพอร์ต/บริการที่ไม่จำเป็น",
    "steps" => [
      "ตรวจสอบกฎ firewall",
      "ปิดพอร์ต/บริการที่ไม่ใช้",
      "ทบทวนเป็นระยะ"
    ]
  ],
  [
    "section" => "Cloud/คลาวด์",
    "icon" => "☁️",
    "text" => "คลาวด์ตั้งค่าปลอดภัยพื้นฐาน (MFA/Logs/Encryption) หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลหรือระบบบนคลาวด์อาจถูกเข้าถึงหรือโจมตีได้ง่าย",
    "fix" => "ตั้งค่าความปลอดภัยพื้นฐานบนคลาวด์",
    "steps" => [
      "เปิดใช้ MFA",
      "เปิด Logging",
      "ตั้งค่า Encryption"
    ]
  ]
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Cyber IRP Risk Assessment (PHP)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/avif" href="logo.avif">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
  <style>
    /* ---------------------------
       ส่วน CSS: ตกแต่งหน้าตาแบบฟอร์ม
       --------------------------- */
    body {
      background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%);
    }
    .cyber-glow {
      /* ใส่เงาให้หัวข้อ */
      text-shadow: 0 2px 8px #38bdf8, 0 0px 2px #0ea5e9;
      letter-spacing: 0.03em;
    }
    .question-card {
      /* กล่องคำถาม */
      background: #fff;
      border: none;
      border-radius: 1.2rem;
      box-shadow: 0 4px 24px 0 #38bdf822;
      max-width: 600px;
      margin: 0 auto;
      transition: box-shadow 0.2s;
      padding: 2.5rem 2rem 2rem 2rem;
      position: relative;
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
      animation: fadeInUp 0.5s;
    }
    @keyframes fadeInUp {
      from { opacity:0; transform: translateY(30px);}
      to { opacity:1; transform: translateY(0);}
    }
    .question-card:after {
      /* เส้น gradient ใต้กล่อง */
      content: "";
      position: absolute;
      left: 1.5rem; right: 1.5rem; bottom: 0;
      height: 4px;
      border-radius: 2px;
      background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
      opacity: 0.12;
    }
    .question-progress {
      /* แถบ progress */
      background: #e0f2fe;
      border-radius: 1rem;
      overflow: hidden;
      height: 12px;
      margin-bottom: 2rem;
      box-shadow: 0 1px 4px #38bdf822;
    }
    .question-progress-bar {
      /* progress bar สีฟ้า */
      background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
      height: 100%;
      transition: width 0.4s cubic-bezier(.4,2,.6,1);
    }
    .radio-group {
      /* กลุ่ม radio button */
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 0.5rem;
    }
    .radio-group label {
      /* ปุ่มตัวเลือก */
      background: #f1f5f9;
      border-radius: 0.5rem;
      padding: 0.45rem 1.2rem 0.45rem 1rem;
      font-weight: 500;
      cursor: pointer;
      border: 1.5px solid transparent;
      transition: border 0.2s, background 0.2s;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 1rem;
    }
    .radio-group input[type="radio"] {
      margin-right: 0.3rem;
    }
    .radio-group input[type="radio"]:checked + span {
      color: #0ea5e9;
      font-weight: 700;
    }
    .radio-group input[type="radio"]:focus + span {
      outline: 2px solid #38bdf8;
    }
    .question-section {
      /* หมวดหมู่ (ถ้ามี) */
      color: #0ea5e9;
      font-weight: 600;
      font-size: 1.05rem;
      margin-bottom: 0.2rem;
      letter-spacing: 0.01em;
    }
    .question-number {
      /* หมายเลขข้อ */
      position: absolute;
      top: 1.2rem;
      right: 2rem;
      color: #b6c7d6;
      font-size: 1.1rem;
      font-weight: 600;
      letter-spacing: 0.04em;
    }
    .question-card input[type="text"] {
      /* ช่องหมายเหตุ */
      background: #f8fafc;
      border: 1.5px solid #e0f2fe;
      border-radius: 0.6rem;
      padding: 0.7rem 1rem;
      font-size: 1rem;
      margin-top: 0.2rem;
      transition: border 0.2s;
      width: 100%;
    }
    .question-card input[type="text"]:focus {
      border-color: #38bdf8;
      outline: none;
      background: #fff;
    }
    .btn-main {
      /* ปุ่มถัดไป */
      background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
      color: #fff;
      font-weight: 600;
      border-radius: 0.7rem;
      padding: 0.7rem 2.2rem;
      box-shadow: 0 2px 8px #38bdf822;
      transition: background 0.2s, box-shadow 0.2s;
      border: none;
    }
    .btn-main:hover, .btn-export:hover {
      transform: translateY(-2px) scale(1.04);
      box-shadow: 0 6px 24px #38bdf822;
    }
    .btn-secondary {
      /* ปุ่มย้อนกลับ */
      background: #f1f5f9;
      color: #0ea5e9;
      font-weight: 600;
      border-radius: 0.7rem;
      padding: 0.7rem 2.2rem;
      border: none;
      transition: background 0.2s;
    }
    .btn-secondary:hover {
      background: #e0f2fe;
    }
    .btn-group > * { margin: 0 0.3rem 0.5rem 0.3rem; }
    @media (max-width: 600px) {
      /* Responsive สำหรับมือถือ */
      .question-card { padding: 1.2rem 0.7rem 1.2rem 0.7rem; }
      .question-number { right: 1rem; top: 0.7rem; }
      .radio-group label { font-size: 0.97rem; padding: 0.4rem 0.8rem 0.4rem 0.7rem; }
    }
    @media print {
      /* สำหรับพิมพ์/Export PDF */
      .print\:hidden { display: none !important; }
      body { background: #fff !important; }
      .question-card { box-shadow: none !important; border: 1px solid #bbb !important; }
      #exportBtns { display: none !important; }
    }
    .panel-header { font-size:1.25rem; font-weight:700; color:#2563eb; background:#f1f5f9; border-radius:0.7rem 0.7rem 0 0; padding:1rem 1.5rem; cursor:pointer; display:flex; align-items:center; justify-content:space-between; }
    .panel-content {
      display: block !important; /* บังคับให้ block เสมอ */
      overflow: hidden;
      max-height: 0;
      transition: max-height 0.4s cubic-bezier(.4,2,.6,1);
    }
    .panel-content.open {
      max-height: 2000px; /* หรือค่าที่มากพอให้เนื้อหาแสดงครบ */
    }
    .panel.active .panel-content { display:block; }
    .panel { margin-bottom:2rem; box-shadow:0 2px 12px #38bdf822; border-radius:0.7rem; }
    .panel-header svg { transition:transform 0.2s; }
    .panel.active .panel-header svg { transform:rotate(90deg);}
    .result-table { width:100%; border-collapse:collapse; margin-bottom:1.5rem; }
    .result-table th, .result-table td { border:1px solid #e0e7ef; padding:0.7em 1em; text-align:left; }
    .result-table th { background:#f1f5f9; font-weight:700; }
    .result-table tr:nth-child(even) { background:#f8fafc; }
    .risk-high { color:#dc2626; font-weight:bold; }
    .risk-medium { color:#d97706; font-weight:bold; }
    .risk-low { color:#16a34a; font-weight:bold; }
    .heatmap-table { border-collapse:collapse; margin:1.5rem auto; }
    .heatmap-table td, .heatmap-table th { width:60px; height:40px; text-align:center; font-weight:600; border:1px solid #e0e7ef; }
    .heatmap-low { background:#bbf7d0; }
    .heatmap-medium { background:#fde68a; }
    .heatmap-high { background:#fecaca; }
    .stepper { border-left:4px solid #38bdf8; margin-left:1.5rem; padding-left:2rem; }
    .stepper-phase { position:relative; margin-bottom:2.5rem; }
    .stepper-phase:last-child { margin-bottom:0; }
    .stepper-dot { position:absolute; left:-2.2rem; top:0.2rem; width:1.3rem; height:1.3rem; background:#38bdf8; border-radius:50%; border:3px solid #fff; box-shadow:0 0 0 2px #38bdf8; }
    .stepper-title { font-size:1.1rem; font-weight:700; color:#0ea5e9; margin-bottom:0.5rem; }
    .stepper-table { margin-bottom:0.7rem; }
    .stepper-table th, .stepper-table td { font-size:0.98rem; }
    .summary-card { background:#f1f5f9; border-radius:0.7rem; padding:1.2rem 1.5rem; margin-bottom:1.5rem; font-size:1.1rem; }
    .export-btns { margin-top:2rem; display:flex; flex-wrap:wrap; gap:0.7rem; justify-content:center; }
    .btn-export { background:#f1f5f9; color:#0ea5e9; font-weight:600; border-radius:0.7rem; padding:0.7rem 2.2rem; border:none; transition:background 0.2s; }
    .btn-export:hover { background:#e0f2fe; }
    /* ---------------------------
       ส่วน CSS: ปรับแต่งสำหรับผลลัพธ์ Pro Report
       --------------------------- */
    .panel-header { background: #f1f5f9; }
    .panel-header span { font-weight: 600; }
    .panel-content { background: #fff; border: 1px solid #e0e7ef; }
    .result-table th {
      background: #f1f5f9;
      color: #0e2233;
      font-weight: 600;
    }
    .result-table td {
      background: #fff;
      color: #0e2233;
    }
    .risk-high { color:#dc2626; font-weight:bold; }
    .risk-medium { color:#d97706; font-weight:bold; }
    .risk-low { color:#16a34a; font-weight:bold; }
    .heatmap-table td, .heatmap-table th {
      background: #f1f5f9;
      color: #0e2233;
      font-weight: 600;
    }
    .stepper-dot { background:#38bdf8; }
    .stepper-title { color:#0ea5e9; }
    /* ---------------------------
       ส่วน CSS: ปรับแต่งสำหรับ Modal
       --------------------------- */
    .modal-content {
      border-radius: 1.2rem;
      box-shadow: 0 4px 24px 0 #38bdf822;
    }
    .modal-header {
      border-bottom: 1px solid #e0e7ef;
    }
    .modal-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #0e2233;
    }
    .modal-body {
      font-size: 1rem;
      color: #0e2233;
    }
    /* ปรับขนาด modal สำหรับหน้าจอเล็ก */
    @media (max-width: 768px) {
      .modal-dialog {
        max-width: 90%;
        margin: 1rem auto;
      }
    }
  </style>
  <!-- เพิ่ม Bootstrap 5 CSS/JS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="max-w-xl mx-auto my-8 p-2">
  <!-- ---------------------------
       ส่วนหัวแบบฟอร์ม (โลโก้/หัวข้อ)
       --------------------------- -->
  <div class="flex flex-col items-center mb-8">
    <!-- โลโก้บริษัทแบบโปร (ใช้ไฟล์ logo.avif) -->
    <img 
      src="logo.avif" 
      alt="HackerProtect Pro Logo" 
      style="
        width:110px;
        margin-bottom:18px;
        border-radius: 22px;
        box-shadow: 0 6px 32px 0 #38bdf855, 0 1px 2px #0ea5e955;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%);
        border: 2.5px solid #38bdf8;
        padding: 8px;
        transition: box-shadow 0.2s;
      "
      class="hover:scale-105 transition-transform duration-200"
    >
    <!-- หัวข้อหลักแบบ Paradise Resort Style -->
    <h1 
      class="text-4xl md:text-5xl font-extrabold text-center mb-2"
      style="
        color: #0e2233;
        letter-spacing: 0.03em;
        text-shadow:
          0 6px 32px #38bdf822,
          0 2px 8px #0ea5e9,
          0 1px 0 #fff,
          0 0 2px #fff;
        font-family: 'Prompt', 'Sarabun', 'Segoe UI', Arial, sans-serif;
        background: none;
        -webkit-background-clip: initial;
        -webkit-text-fill-color: initial;
        background-clip: initial;
      "
    >
      CHECK RISK
      <span 
        class="align-super font-bold"
        style="
          font-size:0.7em;
          margin-left:0.2em;
          letter-spacing:0.05em;
          color: #38bdf8;
          text-shadow:0 2px 8px #38bdf8, 0 0px 2px #0ea5e9;
          font-family: inherit;
        "
      >
    </div>
    <p 
      class="text-center text-cyan-800 text-lg md:text-xl font-medium mb-2"
      style="font-family: 'Prompt', 'Sarabun', 'Segoe UI', Arial, sans-serif;"
    >
      แบบประเมินความพร้อมและความปลอดภัยไซเบอร์ภายในองค์กร
    </p>
  </div>
  <!-- ---------------------------
       ส่วนฟอร์มคำถาม
       --------------------------- -->
  <section id="formSection">
    <!-- Progress bar -->
    <div class="question-progress">
      <div id="progressBar" class="question-progress-bar" style="width:0%"></div>
    </div>
    <form id="assessmentForm" autocomplete="off">
      <div id="questionBox"></div>
      <!-- ปุ่มย้อนกลับ/ถัดไป -->
      <div class="btn-group flex flex-row gap-2 mt-4 justify-center print:hidden">
        <button type="button" id="prevBtn" class="btn-secondary" style="min-width:110px;">ย้อนกลับ</button>
        <button type="button" id="nextBtn" class="btn-main" style="min-width:110px;">ถัดไป</button>
      </div>
    </form>
  </section>
  <!-- =========================
       ผลลัพธ์ Risk Assessment & IRP
       ========================= -->
  <section id="proResultsSection" style="display:none;">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Risk Assessment Results</h2>
    <!-- Collapsible Panels -->
    <div class="panel active" id="panel-risk">
      <div class="panel-header">
        <span>Cybersecurity Risk Assessment</span>
        <svg width="18" height="18" fill="none"><path d="M6 7l3 3 3-3" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round"/></svg>
      </div>
      <div class="panel-content">
        <div class="mb-6">
          <div class="font-bold text-lg mb-2">Risk Scenario Table</div>
          <table class="result-table">
            <thead>
              <tr>
                <th>Asset</th>
                <th>Threat Event</th>
                <th>Vulnerability</th>
                <th>Consequence</th>
              </tr>
            </thead>
            <tbody id="riskScenarioRows"></tbody>
          </table>
        </div>
        <div class="mb-6">
          <div class="font-bold text-lg mb-2">Likelihood & Impact Matrix</div>
          <table class="result-table">
            <thead>
              <tr>
                <th>Asset</th>
                <th>Likelihood (1-3)</th>
                <th>Impact (1-3)</th>
                <th>Risk Level</th>
              </tr>
            </thead>
            <tbody id="riskMatrixRows"></tbody>
          </table>
          <div class="mt-4">
            <div class="font-bold text-lg mb-2">Risk Heatmap</div>
            <table class="heatmap-table">
              <tr>
                <th rowspan="2" style="writing-mode:vertical-lr;transform:rotate(180deg);">Impact</th>
                <th colspan="3">Likelihood</th>
              </tr>
              <tr>
                <th>1</th><th>2</th><th>3</th>
              </tr>
              <tbody id="heatmapGrid"></tbody>
            </table>
          </div>
        </div>
        <div>
          <div class="font-bold text-lg mb-2">Risk Response Table</div>
          <table class="result-table">
            <thead>
              <tr>
                <th>Asset</th>
                <th>Risk Level</th>
                <th>Recommended Response</th>
              </tr>
            </thead>
            <tbody id="riskResponseRows"></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="panel" id="panel-irp">
      <div
        id="irp-toggle"
        class="panel-header"
        tabindex="0"
        role="button"
        aria-expanded="false"
        aria-controls="irp-panel"
        data-testid="irp-toggle"
        style="user-select:none;outline:none;display:flex;align-items:center;justify-content:space-between;cursor:pointer;"
      >
        <span>Incident Response Plan</span>
        <svg id="irp-caret" width="18" height="18" fill="none" style="transition:transform 0.3s;">
          <path d="M6 7l3 3 3-3" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <div
        id="irp-panel"
        class="panel-content"
        data-testid="irp-panel"
        aria-hidden="true"
      >
        <!-- ...เนื้อหา... -->
      </div>
    </div>
    <div class="export-btns print:hidden">
      <button type="button" class="btn-export" id="exportPDF2">Export PDF</button>
      <button type="button" class="btn-export" id="exportCSV2">Export CSV</button>
      <button type="button" class="btn-export" id="exportJSON2">Export JSON</button>
    </div>
  </section>
  <!-- ---------------------------
       ส่วนสรุปผลและปุ่ม Export
       --------------------------- -->
  <section id="summarySection" style="display:none">
    <div id="summary" class="mt-10"></div>
    <div id="exportBtns" class="export-btns print:hidden">
      <button type="button" class="btn-export" id="exportPDF">Export PDF</button>
      <button type="button" class="btn-export" id="exportCSV">Export CSV</button>
      <button type="button" class="btn-export" id="exportJSON">Export JSON</button>
      <button type="button" class="btn-export" onclick="window.print()">Print</button>
    </div>
  </section>
  <!-- ---------------------------
       ส่วน Footer
       --------------------------- -->
  <footer class="mt-12 text-center text-xs text-cyan-700 print:text-black">
    &copy; <?= date('Y') ?> Cyber IRP Risk Assessment | For internal use only
  </footer>
</div>
<!-- เพิ่ม Modal HTML สำหรับรายละเอียดความเสี่ยงสูง -->
<div class="modal fade" id="riskHighModal" tabindex="-1" aria-labelledby="riskHighModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="riskHighModalLabel">รายละเอียดความเสี่ยงสูง</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <div class="modal-body" id="riskHighModalBody">
        <!-- รายละเอียดจะแสดงตรงนี้ -->
      </div>
    </div>
  </div>
</div>
<script>
// ---------------------------
// ส่วน: ค่าคงที่/การตั้งค่า
// ---------------------------
const QUESTIONS = <?php echo json_encode($questions); ?>; // ดึงคำถามจาก PHP
const SCORE_MAP = { yes: 0, partial: 1, no: 2 }; // กำหนดคะแนนแต่ละตัวเลือก
const LABEL_MAP = { yes: "ใช่", partial: "ไม่แน่ใจ", no: "ไม่ใช่" }; // แปลงค่าตัวเลือกเป็นข้อความ
const RISK_LEVEL = { 2: '<span class="risk-high">เสี่ยงสูง</span>', 1: '<span class="risk-medium">เสี่ยงปานกลาง</span>', 0: '<span class="risk-low">เสี่ยงต่ำ</span>' };
const SECTION_ICONS = {
  "การจัดการข้อมูล": "🗂️",
  "การควบคุมสิทธิ์": "🔑",
  "การอัปเดต/ช่องโหว่": "🛡️",
  "การสำรองข้อมูล": "💾",
  "การตรวจจับเหตุการณ์": "🔎",
  "การตอบสนองเหตุการณ์": "🚨",
  "การป้องกันมัลแวร์": "🦠",
  "การบริหารจัดการ": "👔",
  "การปฏิบัติตามกฎหมาย": "📜",
  "โครงสร้างพื้นฐาน": "🌐",
  "Cloud/คลาวด์": "☁️"
};

let current = 0; // ข้อที่กำลังแสดง
let responses = []; // เก็บคำตอบแต่ละข้อ

// ---------------------------
// ฟังก์ชันคืนค่าคำอธิบายศัพท์เฉพาะ (tooltip)
// ---------------------------
function getQuestionHint(q) {
  // ตรวจสอบว่าคำถามมีศัพท์เฉพาะหรือไม่ แล้วคืนคำอธิบาย
  if(q.text.includes("RTO/RPO")) return 'RTO (Recovery Time Objective) คือเวลาสูงสุดที่ระบบต้องกลับมาใช้งานได้หลังเกิดเหตุ<br>RPO (Recovery Point Objective) คือข้อมูลล่าสุดที่ต้องการให้กู้คืนได้';
  if(q.text.includes("2FA") || q.text.includes("MFA")) return '2FA/MFA คือการยืนยันตัวตนแบบสองขั้น เช่น รหัสผ่าน+OTP หรือรหัสผ่าน+แอป';
  if(q.text.includes("SIEM")) return 'SIEM คือระบบรวม log เพื่อวิเคราะห์และแจ้งเตือนเหตุผิดปกติ';
  if(q.text.includes("Immutable")) return 'Immutable Backup คือข้อมูลสำรองที่ไม่สามารถแก้ไขหรือลบได้โดยผู้โจมตี';
  if(q.text.includes("Playbook")) return 'Playbook คือคู่มือหรือขั้นตอนปฏิบัติเมื่อเกิดเหตุการณ์ เช่น ฟิชชิงหรือมัลแวร์';
  if(q.text.includes("BCP/DR")) return 'BCP (Business Continuity Plan) คือแผนความต่อเนื่องทางธุรกิจ<br>DR (Disaster Recovery) คือแผนกู้คืนระบบหลังเกิดเหตุ';
  if(q.text.includes("PDPA")) return 'PDPA คือกฎหมายคุ้มครองข้อมูลส่วนบุคคลของไทย';
  if(q.text.includes("CERT")) return 'CERT คือทีมตอบสนองเหตุการณ์ความปลอดภัยไซเบอร์ (เช่น ThaiCERT)';
  if(q.text.includes("EDR")) return 'EDR คือซอฟต์แวร์ป้องกันและตรวจจับภัยคุกคามบนเครื่องคอมพิวเตอร์';
  if(q.text.includes("Asset Inventory")) return 'Asset Inventory คือรายการอุปกรณ์/ระบบทั้งหมดในองค์กร';
  if(q.text.includes("Firewall")) return 'Firewall คือระบบป้องกันและควบคุมการรับส่งข้อมูลเครือข่าย';
  if(q.text.includes("Cloud") || q.text.includes("คลาวด์")) return 'Cloud คือระบบหรือบริการที่อยู่บนอินเทอร์เน็ต เช่น AWS, Azure, Google Cloud';
  // เพิ่ม tip อื่นๆ ได้ที่นี่
  return "";
}

// ---------------------------
// ฟังก์ชันแสดงคำถามทีละข้อ
// ---------------------------
function renderQuestion(idx) {
  // สร้าง HTML สำหรับแสดงคำถามและตัวเลือก
  const q = QUESTIONS[idx];
  let val = responses[idx]?.choice || "";
  let icon = q.icon || SECTION_ICONS[q.section] || "🔒";
  let html = `<div class="question-card">
    <div style="font-size:2em; margin-bottom:0.2em;">${icon}</div>
    <div class="question-section">${q.section ? icon + " " + q.section : ""}</div>
    <div class="question-number">ข้อที่ ${idx+1} / ${QUESTIONS.length}</div>
    <div class="font-medium text-lg text-slate-800 mb-1" style="line-height:1.5">
      ${q.text}
      ${getQuestionHint(q) ? `<button type="button" tabindex="-1" style="background:none;border:none;cursor:pointer;padding:0;margin-left:6px;" title="ดูคำอธิบาย" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='block'?'none':'block'">
        <span style="display:inline-block;width:1.2em;height:1.2em;background:#e0f2fe;color:#0ea5e9;border-radius:50%;font-size:0.95em;text-align:center;line-height:1.2em;font-weight:bold;">?</span>
      </button>
      <span class="question-hint" style="display:none;background:#f1f5f9;border-radius:0.5em;padding:0.6em 1em;margin-top:0.5em;display:block;color:#0ea5e9;font-size:0.97em;">${getQuestionHint(q)}</span>` : ""}
    </div>
    <div class="radio-group">`;
  // วนลูปสร้าง radio button สำหรับแต่ละตัวเลือก
  [["yes","ใช่"],["partial","ไม่แน่ใจ"],["no","ไม่ใช่"]].forEach(([v,l])=>{
    html += `<label>
      <input type="radio" name="choice" value="${v}" class="accent-cyan-500" ${val===v?"checked":""}>
      <span>${l}</span>
    </label>`;
  });
  html += `</div>
    <input type="text" name="comment" placeholder="หมายเหตุ (ถ้ามี)" value="${responses[idx]?.comment ? responses[idx].comment.replace(/"/g,'&quot;') : ""}">
  </div>`;
  // แสดงผลใน questionBox
  document.getElementById('questionBox').innerHTML = html;
  // อัปเดต progress bar
  document.getElementById('progressBar').style.width = `${Math.round((idx+1)/QUESTIONS.length*100)}%`;
  // ซ่อนปุ่มย้อนกลับถ้าเป็นข้อแรก
  document.getElementById('prevBtn').style.display = idx === 0 ? "none" : "";
  // เปลี่ยนข้อความปุ่มถัดไปถ้าเป็นข้อสุดท้าย
  document.getElementById('nextBtn').textContent = idx === QUESTIONS.length-1 ? "ดูผลสรุป" : "ถัดไป";
}

// ---------------------------
// ฟังก์ชันบันทึกคำตอบปัจจุบัน
// ---------------------------
function saveCurrent() {
  // อ่านค่าที่เลือกและหมายเหตุ แล้วเก็บลง responses
  const q = QUESTIONS[current];
  const choice = document.querySelector('input[name="choice"]:checked');
  const comment = document.querySelector('input[name="comment"]').value;
  if(choice) {
    responses[current] = { choice: choice.value, comment };
  }
}

// ---------------------------
// ฟังก์ชันแสดงผลสรุป
// ---------------------------
function showSummary() {
  document.getElementById('summarySection').style.display = "block";

  let ordered = [];
  // --- แยกข้อเสี่ยงตามระดับ ---
  let high = [], medium = [], low = [];
  let total = 0;
  for(let i=0;i<QUESTIONS.length;i++) {
    const ans = responses[i]?.choice;
    const score = SCORE_MAP[ans];
    if(score > 0) {
      total += score;
      if(score === 2) high.push(i);
      else if(score === 1) medium.push(i);
      else low.push(i);
    }
  }
  ordered = [...high, ...medium, ...low];

  // --- Group by section for readability ---
  let sectionMap = {};
  for(const i of ordered) {
    const sec = QUESTIONS[i].section || "อื่นๆ";
    if(!sectionMap[sec]) sectionMap[sec] = [];
    sectionMap[sec].push(i);
  }

  let html = `<div style="
  background: #f8fafc;
  border-radius: 1.5rem;
  padding: 2.5rem 1.5rem;
  box-shadow: 0 4px 32px #38bdf822;
  font-family: 'Prompt', 'Sarabun', 'Segoe UI', Arial, sans-serif;
  ">
  <!-- <h2 style="
    font-size:2.1rem;
    font-weight: bold;
    color: #0e2233;
    margin-bottom: 2rem;
    text-align:center;
    letter-spacing:0.01em;
    ">สรุปข้อที่ควรแก้ไข/มีความเสี่ยง (เรียงตามระดับและหมวด)</h2> -->
  <div style="display:flex; flex-direction:column; gap:2.5rem;">`;

  if (ordered.length === 0) {
    html += `<div style="margin-top:2.5rem; text-align:center; color:#16a34a; font-size:1.3em; font-weight:600;">
      ไม่พบข้อที่ต้องแก้ไขหรือมีความเสี่ยงสูง/ปานกลาง
    </div>`;
  } else {
    for(const sec in sectionMap) {
      let secIcon = SECTION_ICONS[sec] || "🔒";
      html += `<div style="margin-bottom:1.5em;">
        <div style="font-size:1.25em; font-weight:bold; color:#0ea5e9; margin-bottom:0.7em; display:flex;align-items:center;gap:0.5em;">
          <span style="font-size:1.5em;">${secIcon}</span> <u>${sec}</u>
        </div>
        <div style="display:flex; flex-direction:column; gap:1.5rem;">`;
      for(const i of sectionMap[sec]) {
        const ans = responses[i]?.choice;
        const score = SCORE_MAP[ans];
        const risk = QUESTIONS[i].risk;
        const fix = QUESTIONS[i].fix;
        const steps = QUESTIONS[i].steps;
        const comment = responses[i]?.comment ? `<div style="margin-top:0.7em; font-size:1.05em; color:#64748b;"><b>หมายเหตุ:</b> ${responses[i].comment}</div>` : "";
        let riskColor = score === 2 ? "#dc2626" : score === 1 ? "#d97706" : "#16a34a";
        let riskLabel = score === 2 ? "เสี่ยงสูง" : score === 1 ? "เสี่ยงปานกลาง" : "เสี่ยงต่ำ";
        let riskIcon = score === 2 ? "🔥" : score === 1 ? "⚠️" : "✅";
        html += `
        <div style="
          background: #fff;
          border-radius: 1.2rem;
          box-shadow: 0 2px 12px #38bdf822;
          padding: 1.2rem 1.2rem 1.2rem 1.2rem;
          font-size: 1.13rem;
          color: #0e2233;
          border-left: 8px solid ${riskColor};
          display: flex;
          flex-direction: column;
          gap: 0.7em;
          ">
          <div style="display:flex;align-items:center;gap:0.5em;">
            <span style="font-size:1.3em;color:${riskColor};">${riskIcon}</span>
            <span style="font-weight:600;">ข้อที่ ${i+1}:</span>
            <span>${QUESTIONS[i].text}</span>
          </div>
          <div>
            <span style="font-size:1.05em;"><b>คำตอบ:</b> <span style="color:#0ea5e9;">${LABEL_MAP[ans]||"-"}</span></span>
            <span style="margin-left:1em; font-size:1.05em;">(คะแนน: <span class="score" style="color:#0ea5e9;">${score}</span>)</span>
          </div>
          <div>
            <b>ระดับความเสี่ยง:</b> <span style="font-size:1.05em; color:${riskColor}; font-weight:bold;">${riskLabel}</span>
          </div>
          ${comment}
          <details style="margin-top:0.5em;">
            <summary style="cursor:pointer;font-weight:600;color:#0ea5e9;">รายละเอียดความเสี่ยงและแนวทางแก้ไข</summary>
            <div style="margin-top:0.7em; color:#dc2626; font-size:1.05em;">
              <b>⚠️ ความเสี่ยง:</b> ${risk}
            </div>
            <div style="margin-top:0.5em; color:#0ea5e9; font-size:1.05em;">
              <b>💡 คำแนะนำ:</b> ${fix}
            </div>
            <div style="margin-top:0.5em;">
              <b style="color:#0ea5e9;">🔹 ขั้นตอนการแก้ไข:</b>
              <ul style="margin-left:1.5em; margin-top:0.3em; font-size:1.03em; color:#334155;">
                ${steps.map(step=>`<li style="margin-bottom:0.2em;">${step}</li>`).join("")}
              </ul>
            </div>
          </details>
        </div>`;
      }
      html += `</div></div>`;
    }
  }

  html += `</div>
    <div style="margin-top:2.5rem; font-size:1.3em; font-weight:bold; text-align:center;">
      คะแนนรวม (เฉพาะข้อเสี่ยง): <span class="score" style="color:#0ea5e9;">${total}</span> / ${QUESTIONS.length*2}
    </div>
    <div style="margin-top:0.7em; font-size:1.15em; text-align:center;">
      ระดับความเสี่ยงรวม: ${
        total >= QUESTIONS.length*1.5 ? '<span class="risk-high" style="font-size:1.1em;">ความเสี่ยงสูง</span>' :
        total >= QUESTIONS.length*0.5 ? '<span class="risk-medium" style="font-size:1.1em;">ความเสี่ยงปานกลาง</span>' :
        '<span class="risk-low" style="font-size:1.1em;">ความเสี่ยงต่ำ</span>'
      }
    </div>
  </div>`;
  document.getElementById('summary').innerHTML = html;
  document.getElementById('summary').style.display = "block";

  // แสดง pro results ต่อท้าย
  renderProResults(responses, QUESTIONS);
  setTimeout(()=>document.getElementById('proResultsSection').scrollIntoView({behavior:"smooth"}), 200);
}

// ---------------------------
// ฟังก์ชันเรียก AI จาก aiforthai (API)
// ---------------------------
async function callAISummary() {
  // สร้างข้อความสรุปคำตอบ
  let summaryText = QUESTIONS.map((q,i) => {
    const ans = responses[i]?.choice;
    if(ans) return `ข้อ: ${q.text}\nคำตอบ: ${LABEL_MAP[ans]}\n`;
    return '';
  }).filter(Boolean).join('\n');
  try {
    const res = await fetch("https://api.aiforthai.in.th/summarize", {
      method: "POST",
      headers: {
        "Apikey": "SyvIiGOP07EK6fIcQdS20UWDjs79G7gP", // <-- ใช้ API KEY ที่ให้มา
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: "text=" + encodeURIComponent(summaryText)
    });
    const data = await res.json();
    if(data && data.summary) {
      document.getElementById('aiSummary').innerText = data.summary;
    } else {
      document.getElementById('aiSummary').innerText = "ไม่สามารถวิเคราะห์ผลลัพธ์ได้ในขณะนี้";
    }
  } catch(e) {
    document.getElementById('aiSummary').innerText = "เกิดข้อผิดพลาดในการเชื่อมต่อ AI";
  }
}

// ---------------------------
// จัดการปุ่มถัดไป/ย้อนกลับ
// ---------------------------
document.getElementById('nextBtn').onclick = function() {
  // เมื่อกดถัดไป: บันทึกคำตอบ, ไปข้อถัดไป หรือแสดงสรุป
  saveCurrent();
  if(current < QUESTIONS.length-1) {
    current++;
    renderQuestion(current);
  } else {
    // ตรวจสอบว่าตอบครบทุกข้อหรือยัง
    let allAnswered = QUESTIONS.every((q,i) => responses[i]?.choice);
    if(allAnswered) {
      showSummary();
      // scroll ไปยังสรุปผล
      setTimeout(()=>document.getElementById('summarySection').scrollIntoView({behavior:"smooth"}), 200);
    } else {
      // ถ้ายังไม่ครบ ให้ไปข้อแรกที่ยังไม่ตอบ
      let firstUnanswered = QUESTIONS.findIndex((q,i) => !responses[i]?.choice);
      if(firstUnanswered !== -1) {
        current = firstUnanswered;
        renderQuestion(current);
        alert("กรุณาตอบทุกข้อก่อนดูผลสรุป");
      }
    }
  }
};
document.getElementById('prevBtn').onclick = function() {
  // เมื่อกดย้อนกลับ: บันทึกคำตอบ, ย้อนกลับไปข้อก่อนหน้า
  saveCurrent();
  if(current > 0) {
    current--;
    renderQuestion(current);
  }
};
document.getElementById('assessmentForm').onsubmit = function(e) {
  // ป้องกันการ submit ฟอร์มแบบปกติ
  e.preventDefault();
  return false;
};

// ---------------------------
// เริ่มต้นแสดงคำถามข้อแรก
// ---------------------------
renderQuestion(current);

// ====== PRO REPORT LOGIC ======
function getRiskData(responses, QUESTIONS) {
  // For demo: asset = question, threat = risk, vuln = fix, consequence = risk
  // Likelihood: 1=ใช่, 2=ไม่แน่ใจ, 3=ไม่ใช่
  // Impact: ถ้ามีคำว่า "ข้อมูล", "ระบบ", "สูญหาย", "โจมตี" ให้ 3, อื่นๆ 2
  // Risk Level: High (>=7), Medium (>=4), Low (<4)
  // Response: High=Mitigate/Avoid, Medium=Mitigate/Transfer, Low=Accept/Monitor
  let riskRows = [];
  let matrixRows = [];
  let responseRows = [];
  let heatmap = [[[],[],[]],[[],[],[]],[[],[],[]]]; // [impact-1][likelihood-1]
  for(let i=0;i<QUESTIONS.length;i++) {
    const ans = responses[i]?.choice;
    if(!ans) continue;
    const asset = QUESTIONS[i].text;
    const threat = QUESTIONS[i].risk;
    const vuln = QUESTIONS[i].fix;
    const consequence = QUESTIONS[i].risk;
    const likelihood = ans==="yes"?1 : ans==="partial"?2 : 3;
    let impact = /ข้อมูล|ระบบ|สูญหาย|โจมตี|รั่วไหล|ฟื้นฟู/.test(threat)?3:2;
    let riskScore = likelihood * impact;
    let riskLevel = riskScore>=7?"High":riskScore>=4?"Medium":"Low";
    let response = riskLevel==="High"?"Mitigate / Avoid":riskLevel==="Medium"?"Mitigate / Transfer":"Accept / Monitor";
    riskRows.push({asset,threat,vuln,consequence});
    matrixRows.push({asset,likelihood,impact,riskLevel});
    responseRows.push({asset,riskLevel,response});
    heatmap[impact-1][likelihood-1].push(riskLevel);
  }
  return {riskRows, matrixRows, responseRows, heatmap};
}

function getIRPData(responses, QUESTIONS) {
  // Incident type: ถ้าตอบ "ไม่ใช่" ใน 2FA, Patch, Backup, Log, Antivirus, IRP plan, Incident contact, Playbook, Segmentation, Firewall, Cloud
  // (index: 3,7,11,14,15,16,17,18,22,23,24)
  let types = [];
  if(responses[3]?.choice==="no") types.push("Account Compromise");
  if(responses[7]?.choice==="no") types.push("Vulnerability Exploitation");
  if(responses[11]?.choice==="no") types.push("Data Loss / Ransomware");
  if(responses[14]?.choice==="no"||responses[15]?.choice==="no") types.push("Malware Outbreak");
  if(responses[16]?.choice==="no"||responses[17]?.choice==="no") types.push("Incident Response Weakness");
  if(responses[18]?.choice==="no"||responses[22]?.choice==="no") types.push("Network Intrusion");
  if(responses[23]?.choice==="no"||responses[24]?.choice==="no") types.push("Cloud Security Issue");
  if(types.length===0) types.push("General Security Weakness");

  // CIRT Team Roles (ตัวอย่าง)
  const cirtRoles = [
    {name:"นายสมชาย", role:"CIRT Lead", resp:"ควบคุมการตอบสนองเหตุการณ์และตัดสินใจหลัก"},
    {name:"นางสาวพรทิพย์", role:"Technical Lead", resp:"วิเคราะห์เทคนิคและแก้ไขปัญหา"},
    {name:"นายวิทยา", role:"Compliance Officer", resp:"ดูแลข้อกำหนด กฎหมาย และการรายงาน"},
    {name:"คุณอรทัย", role:"IT Support", resp:"สนับสนุนการกู้คืนระบบและประสานงาน"},
  ];

  // IRP Phases & Actions
  const phases = [
    {
      phase:"Preparation",
      actions:[
        {task:"จัดทำนโยบายและแผน IRP", who:"CIRT Lead"},
        {task:"อบรมทีมงานและเตรียมเครื่องมือ", who:"Compliance Officer"},
        {task:"ทดสอบแผนและช่องทางติดต่อ", who:"CIRT Lead, IT Support"}
      ]
    },
    {
      phase:"Detection & Analysis",
      actions:[
        {task:"ตรวจจับเหตุการณ์ผิดปกติจาก Log/SIEM", who:"Technical Lead"},
        {task:"วิเคราะห์เหตุการณ์และประเมินผลกระทบ", who:"Technical Lead, CIRT Lead"},
        {task:"แจ้งเตือนทีมที่เกี่ยวข้อง", who:"CIRT Lead"}
      ]
    },
    {
      phase:"Containment & Recovery",
      actions:[
        {task:"จำกัดขอบเขตเหตุการณ์", who:"Technical Lead"},
        {task:"กู้คืนระบบ/ข้อมูลจากสำรอง", who:"IT Support"},
        {task:"ตรวจสอบความสมบูรณ์ของระบบ", who:"Technical Lead, IT Support"}
      ]
    },
       {
      phase:"Post-Incident Activities",
      actions:[
        {task:"สรุปบทเรียนและปรับปรุงแผน", who:"CIRT Lead, Compliance Officer"},
        {task:"รายงานต่อผู้บริหาร/หน่วยงานกำกับ", who:"Compliance Officer"},
        {task:"อบรมและสื่อสารกับบุคลากร", who:"CIRT Lead"}
      ]
    }
  ];
  return {types, cirtRoles, phases};
}

function renderProResults(responses, QUESTIONS) {
  const risk = getRiskData(responses, QUESTIONS);
   const riskOrder = { "High": 1, "Medium": 2, "Low": 3 };
  const sortedMatrixRows = [...risk.matrixRows].sort((a, b) => {
    return riskOrder[a.riskLevel] - riskOrder[b.riskLevel];
  });

  // Risk Scenario Table
  document.getElementById('riskScenarioRows').innerHTML = risk.riskRows.map(r=>`
    <tr>
      <td>${r.asset}</td>
      <td>${r.threat}</td>
      <td>${r.vuln}</td>
      <td>${r.consequence}</td>
    </tr>
  `).join("");

  // --- NEW: Answer Table ---
  let answerTableHtml = `
    <div class="mb-6">
      <div class="font-bold text-lg mb-2">Answer Table</div>
      <table class="result-table">
        <thead>
          <tr>
            <th>Asset</th>
            <th>Answer</th>
          </tr>
        </thead>
        <tbody>
          ${QUESTIONS.map((q, idx) => {
            const ans = responses[idx]?.choice;
            let label = ans === "yes" ? "ใช่" : ans === "partial" ? "ไม่แน่ใจ" : ans === "no" ? "ไม่ใช่" : "-";
            return `<tr>
              <td>${q.text}</td>
              <td>${label}</td>
            </tr>`;
          }).join("")}
        </tbody>
      </table>
    </div>
  `;
  // แทรก Answer Table ก่อน Risk Level Table
  document.getElementById('riskMatrixRows').parentElement.insertAdjacentHTML('beforebegin', answerTableHtml);

  // Likelihood & Impact Matrix
  document.getElementById('riskMatrixRows').innerHTML = sortedMatrixRows.map((r, idx)=>{
    const qIdx = QUESTIONS.findIndex(q=>q.text===r.asset);
    return `
      <tr>
        <td>${r.asset}</td>
        <td>${r.likelihood}</td>
        <td>${r.impact}</td>
        <td class="risk-${r.riskLevel.toLowerCase()}">
          ${
            r.riskLevel === "High"
            ? `<span style="color:#dc2626;cursor:pointer;text-decoration:underline;font-weight:bold;" class="show-detail" data-idx="${qIdx}" data-risk="high">HIGH</span>`
            : r.riskLevel === "Medium"
            ? `<span style="color:#d97706;cursor:pointer;text-decoration:underline;font-weight:bold;" class="show-detail" data-idx="${qIdx}" data-risk="medium">MEDIUM</span>`
            : `<span style="color:#16a34a;font-weight:bold;">LOW</span>`
          }
        </td>
      </tr>
    `;
  }).join("");

  // --- PRO Risk Heatmap ---
  // Legend
  const legend = `
    <div style="margin-bottom:1em;display:flex;gap:1.5em;align-items:center;">
      <span><span style="display:inline-block;width:1.2em;height:1.2em;background:#fecaca;border-radius:0.3em;margin-right:0.3em;border:1px solid #dc2626;"></span>High</span>
      <span><span style="display:inline-block;width:1.2em;height:1.2em;background:#fde68a;border-radius:0.3em;margin-right:0.3em;border:1px solid #d97706;"></span>Medium</span>
      <span><span style="display:inline-block;width:1.2em;height:1.2em;background:#bbf7d0;border-radius:0.3em;margin-right:0.3em;border:1px solid #16a34a;"></span>Low</span>
      <span style="margin-left:1.5em;color:#64748b;font-size:0.97em;">คลิกหรือชี้ที่ช่องเพื่อดูรายละเอียด</span>
    </div>
  `;
  // ใส่ legend ก่อนตาราง heatmap
  const heatmapTable = document.querySelector('.heatmap-table');
  if (heatmapTable && !document.getElementById('heatmapLegend')) {
    heatmapTable.insertAdjacentHTML('beforebegin', `<div id="heatmapLegend">${legend}</div>`);
  }

  // Heatmap interactive
  let heatmapHtml = "";
  for(let impact=3;impact>=1;impact--) {
    heatmapHtml += "<tr>";
    for(let likelihood=1;likelihood<=3;likelihood++) {
      let levels = risk.heatmap[impact-1][likelihood-1];
      // หา asset ทั้งหมดใน cell นี้
      let assets = sortedMatrixRows.filter(r=>r.likelihood===likelihood && r.impact===impact);
      let cellClass = "heatmap-low";
      let riskType = "-";
      if(levels.includes("High")) { cellClass = "heatmap-high"; riskType = "High"; }
      else if(levels.includes("Medium")) { cellClass = "heatmap-medium"; riskType = "Medium"; }
      else if(levels.includes("Low")) { cellClass = "heatmap-low"; riskType = "Low"; }
      let count = assets.length;
      let assetList = assets.map(r=>`<li>${r.asset}</li>`).join("");
      heatmapHtml += `
        <td class="${cellClass} heatmap-cell" style="cursor:pointer;position:relative;" 
            data-impact="${impact}" data-likelihood="${likelihood}" data-risk="${riskType}" data-assets='${JSON.stringify(assets.map(r=>r.asset))}'>
          ${count ? `<b>${count}</b>` : "-"}
          <div class="heatmap-tooltip" style="display:none;position:absolute;z-index:10;left:50%;top:110%;transform:translateX(-50%);background:#fff;border:1px solid #e0e7ef;border-radius:0.7em;box-shadow:0 2px 12px #38bdf822;padding:1em;min-width:220px;">
            <div style="font-weight:bold;color:#0ea5e9;">Impact: ${impact}, Likelihood: ${likelihood}</div>
            <div style="margin:0.3em 0 0.5em 0;"><span style="font-weight:bold;color:${riskType==="High"?"#dc2626":riskType==="Medium"?"#d97706":"#16a34a"};">${riskType}</span></div>
            ${count ? `<div style="font-size:0.97em;"><b>Asset:</b><ul style="margin:0.2em 0 0 1.2em;">${assetList}</ul></div>` : `<div style="color:#64748b;">ไม่มีรายการ</div>`}
          </div>
        </td>
      `;
    }
    heatmapHtml += "</tr>";
  }
  document.getElementById('heatmapGrid').innerHTML = heatmapHtml;

  // Tooltip ฟังก์ชัน
  document.querySelectorAll('.heatmap-cell').forEach(cell=>{
    cell.addEventListener('mouseenter', function() {
      this.querySelector('.heatmap-tooltip').style.display = 'block';
    });
    cell.addEventListener('mouseleave', function() {
      this.querySelector('.heatmap-tooltip').style.display = 'none';
    });
    cell.addEventListener('click', function() {
      const tooltip = this.querySelector('.heatmap-tooltip');
      tooltip.style.display = tooltip.style.display === 'block' ? 'none' : 'block';
    });
  });

  // Risk Response Table
  document.getElementById('riskResponseRows').innerHTML = risk.responseRows.map(r=>`
    <tr>
      <td>${r.asset}</td>
      <td class="risk-${r.riskLevel.toLowerCase()}">${r.riskLevel.toUpperCase()}</td>
      <td>${r.response}</td>
    </tr>
  `).join("");

  // IRP (เหมือนเดิม)
  const irp = getIRPData(responses, QUESTIONS);
  document.getElementById('incidentSummary').innerHTML = `
    <b>Incident Classification:</b> <span style="color:#0ea5e9">${irp.types.join(", ")}</span>
  `;
  document.getElementById('cirtRolesRows').innerHTML = irp.cirtRoles.map(r=>`
    <tr>
      <td>${r.name}</td>
      <td>${r.role}</td>
      <td>${r.resp}</td>
    </tr>
  `).join("");
  document.getElementById('irpStepper').innerHTML = irp.phases.map((p,i)=>`
    <div class="stepper-phase">
      <div class="stepper-dot"></div>
      <div class="stepper-title">${i+1}. ${p.phase}</div>
      <table class="result-table stepper-table">
        <thead>
          <tr>
            <th>Key Action</th>
            <th>Responsible</th>
          </tr>
        </thead>
        <tbody>
          ${p.actions.map(a=>`
            <tr>
              <td>${a.task}</td>
              <td>${a.who}</td>
            </tr>
          `).join("")}
        </tbody>
      </table>
    </div>
  `).join("");
  document.getElementById('proResultsSection').style.display = "block";

  // Event: เมื่อคลิก HIGH หรือ MEDIUM (ที่เป็นตัวอักษร)
  setTimeout(()=>{
    document.querySelectorAll('.show-detail').forEach(cell=>{
      cell.onclick = function() {
        const i = +cell.getAttribute('data-idx');
        const riskType = cell.getAttribute('data-risk');
        const q = QUESTIONS[i];
        const ans = responses[i]?.choice;
        const score = SCORE_MAP[ans];
        let riskColor = riskType === "high" ? "#dc2626" : "#d97706";
        let riskLabel = riskType === "high" ? "เสี่ยงสูง" : "เสี่ยงปานกลาง";
        let html = `
          <div class="mb-3">
            <span style="font-size:1.2em;font-weight:bold;">${q.text}</span>
          </div>
          <div class="mb-2"><b>คำตอบ:</b> <span style="color:#0ea5e9;">${LABEL_MAP[ans]||"-"}</span> (คะแนน: ${score})</div>
          <div class="mb-2"><b>ระดับความเสี่ยง:</b> <span style="color:${riskColor};font-weight:bold;">${riskLabel}</span></div>
          <div class="mb-2"><b>ความเสี่ยง:</b> <span style="color:${riskColor};">${q.risk}</span></div>
          <div class="mb-2"><b>คำแนะนำ:</b> <span style="color:#0ea5e9;">${q.fix}</span></div>
          <div class="mb-2"><b>ขั้นตอนการแก้ไข:</b>
            <ul>${q.steps.map(step=>`<li>${step}</li>`).join("")}</ul>
          </div>
          ${responses[i]?.comment ? `<div class="mb-2"><b>หมายเหตุ:</b> ${responses[i].comment}</div>` : ""}
        `;
        document.getElementById('riskHighModalBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('riskHighModal')).show();
      };
    });
  }, 100);
}

// ---------------------------
// ส่วน: จัดการสถานะเปิดปิดของ IRP panel
// ---------------------------
(function() {
  const toggle = document.getElementById('irp-toggle');
  const panel = document.getElementById('irp-panel');
  const caret = document.getElementById('irp-caret');
  const storageKey = 'incidentResponsePanel:open';

  // โหลดสถานะจาก localStorage
  let open = localStorage.getItem(storageKey) === "1";
  setPanel(open);

  function setPanel(isOpen) {
    if(isOpen) {
      panel.classList.add('open');
      panel.setAttribute('aria-hidden', 'false');
      toggle.setAttribute('aria-expanded', 'true');
      caret.style.transform = "rotate(90deg)";
      localStorage.setItem(storageKey, "1");
    } else {
      panel.classList.remove('open');
      panel.setAttribute('aria-hidden', 'true');
      toggle.setAttribute('aria-expanded', 'false');
      caret.style.transform = "rotate(0deg)";
      localStorage.setItem(storageKey, "0");
    }
  }

  function handleToggle(e) {
    if(e.type === "click" || e.key === "Enter" || e.key === " ") {
      open = !open;
      setPanel(open);
      e.preventDefault();
    }
  }
  toggle.addEventListener('click', handleToggle);
  toggle.addEventListener('keydown', handleToggle);

  // Responsive: ปรับ max-height เมื่อขนาด panel เปลี่ยน
  window.addEventListener('resize', ()=>{
    if(open) setPanel(true);
  });
})();
</script>
</body>
</html>