//
//  ViewControllerRepresentable.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 23.11.2024.
//

import Foundation
import UIKit
import SwiftUI

struct ViewControllerRepresentable<VC: UIViewController>: UIViewControllerRepresentable {
    
    let viewController: VC
    
    init(viewController: VC) {
        self.viewController = viewController
    }
    
    func makeUIViewController(context: Context) -> VC {
        return viewController
    }
    
    func updateUIViewController(_ uiViewController: VC, context: Context) {
    }
}
