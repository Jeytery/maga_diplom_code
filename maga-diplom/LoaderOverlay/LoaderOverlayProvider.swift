//
//  LoaderOverlayProvider.swift
//  role-cards
//
//  Created by Dmytro Ostapchenko on 17.02.2024.
//

import Foundation
import UIKit

class LoaderOverlayProvider {
    
    private init() {
        if let window = getWindow() {
            self.window = window
        }
        else {
            print("FatalError: LoaderOverlayProvider hasn't get window in init()")
        }
    }
    
    static let shared = LoaderOverlayProvider()
    
    private var window: UIWindow!
    private var isBeingShown: Bool = false
    private var currentLoader: LoaderOverlayView!
    
    func overlay() {
        if isBeingShown { return }
        currentLoader = LoaderOverlayView(frame: .zero)
        window.addSubview(currentLoader)
        currentLoader.translatesAutoresizingMaskIntoConstraints = false
        currentLoader.topAnchor.constraint(equalTo: window.topAnchor).isActive = true
        currentLoader.bottomAnchor.constraint(equalTo: window.bottomAnchor).isActive = true
        currentLoader.leftAnchor.constraint(equalTo: window.leftAnchor).isActive = true
        currentLoader.rightAnchor.constraint(equalTo: window.rightAnchor).isActive = true
        currentLoader.alpha = 0
      
        UIView.animate(withDuration: 0.3, animations: {
            [weak self] in
            self?.currentLoader.alpha = 1
        })
    }
    
    func remove() {
        guard let currentLoader = currentLoader else { return }
        UIView.animate(withDuration: 0.3, animations: {
            currentLoader.alpha = 0
        }, completion: { _ in
            currentLoader.removeFromSuperview()
        })
    }
    
    private func getWindow() -> UIWindow? {
        return UIApplication.shared.keyWindow
    }
}

